<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Send new members with a paid plan straight to checkout; everyone else to the portal.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        /** @var User $user */
        $user = $request->user();
        $membership = $user->latestMembership;

        if ($membership?->needsPayment()) {
            return redirect()->route('memberships.checkout', $membership);
        }

        return redirect()->route('portal');
    }
}
