<?php

namespace App\Services;

use App\Contracts\Payable;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentGatewayException;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SslCommerzGateway
{
    public function __construct(
        private readonly ?string $storeId,
        private readonly ?string $storePassword,
        private readonly bool $sandbox,
    ) {}

    /**
     * Create a pending payment and open a gateway session for it.
     *
     * @return string The SSLCommerz page the customer is sent to.
     *
     * @throws PaymentGatewayException
     */
    public function initiate(Payable&Model $payable, User $user): string
    {
        $payment = new Payment([
            'amount' => $payable->paymentAmount(),
            'currency' => 'BDT',
            'tran_id' => 'RUG'.Str::upper(Str::random(16)),
            'status' => PaymentStatus::Pending,
        ]);
        $payment->user()->associate($user);
        $payment->payable()->associate($payable);
        $payment->save();

        $user->loadMissing('profile');

        try {
            $response = Http::asForm()->timeout(30)->post($this->baseUrl().'/gwprocess/v4/api.php', [
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'total_amount' => $payment->amount,
                'currency' => $payment->currency,
                'tran_id' => $payment->tran_id,
                'success_url' => route('payments.sslcommerz.success'),
                'fail_url' => route('payments.sslcommerz.fail'),
                'cancel_url' => route('payments.sslcommerz.cancel'),
                'ipn_url' => route('payments.sslcommerz.ipn'),
                'cus_name' => $user->name,
                'cus_email' => $user->email,
                'cus_phone' => $user->profile->phone ?? 'N/A',
                'cus_add1' => $user->profile->location ?? 'Bangladesh',
                'cus_city' => 'Rajshahi',
                'cus_country' => 'Bangladesh',
                'shipping_method' => 'NO',
                'product_name' => $payable->paymentDescription(),
                'product_category' => $payable->paymentCategory(),
                'product_profile' => 'non-physical-goods',
            ]);
        } catch (ConnectionException $exception) {
            $payment->update(['status' => PaymentStatus::Failed]);

            throw new PaymentGatewayException('Could not reach the payment gateway.', previous: $exception);
        }

        $gatewayPageUrl = $response->json('GatewayPageURL');

        if ($response->json('status') !== 'SUCCESS' || ! is_string($gatewayPageUrl) || $gatewayPageUrl === '') {
            $payment->forceFill([
                'status' => PaymentStatus::Failed,
                'gateway_response' => $response->json(),
            ])->save();

            Log::warning('SSLCommerz session init failed', ['tran_id' => $payment->tran_id, 'reason' => $response->json('failedreason')]);

            throw new PaymentGatewayException('The payment gateway did not accept the request.');
        }

        return $gatewayPageUrl;
    }

    /**
     * Confirm a payment with the SSLCommerz validation API and mark it paid.
     * Safe to call more than once for the same transaction (success redirect + IPN).
     */
    public function confirm(string $tranId, ?string $valId): ?Payment
    {
        $payment = Payment::query()->where('tran_id', $tranId)->first();

        if ($payment === null || blank($valId)) {
            return $payment;
        }

        if (! $payment->isPending()) {
            return $payment;
        }

        $validation = $this->validate($valId);

        if (! $this->validationMatches($validation, $payment)) {
            Log::warning('SSLCommerz validation mismatch', ['tran_id' => $tranId, 'status' => $validation['status'] ?? null]);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $validation, $valId): Payment {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if (! $locked->isPending()) {
                return $locked;
            }

            $locked->forceFill([
                'status' => PaymentStatus::Paid,
                'val_id' => $valId,
                'card_type' => $validation['card_type'] ?? null,
                'paid_at' => now(),
                'gateway_response' => $validation,
            ])->save();

            $locked->payable->markAsPaid($locked);

            return $locked;
        });
    }

    /**
     * Record a failed or cancelled payment, unless it was already paid.
     */
    public function close(string $tranId, PaymentStatus $status): ?Payment
    {
        $payment = Payment::query()->where('tran_id', $tranId)->first();

        if ($payment?->isPending()) {
            $payment->update(['status' => $status]);
        }

        return $payment;
    }

    /**
     * @return array<string, mixed>
     */
    private function validate(string $valId): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl().'/validator/api/validationserverAPI.php', [
                'val_id' => $valId,
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
                'format' => 'json',
            ]);
        } catch (ConnectionException) {
            return [];
        }

        return (array) $response->json();
    }

    /**
     * @param  array<string, mixed>  $validation
     */
    private function validationMatches(array $validation, Payment $payment): bool
    {
        return in_array($validation['status'] ?? null, ['VALID', 'VALIDATED'], true)
            && ($validation['tran_id'] ?? null) === $payment->tran_id
            && ($validation['currency_type'] ?? $validation['currency'] ?? null) === $payment->currency
            && abs((float) ($validation['currency_amount'] ?? $validation['amount'] ?? 0) - $payment->amount) < 0.01;
    }

    private function baseUrl(): string
    {
        return $this->sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
    }
}
