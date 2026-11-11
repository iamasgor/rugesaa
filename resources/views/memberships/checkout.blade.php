<x-layouts.site title="Pay membership fee">
    <div class="mx-auto flex max-w-xl flex-col gap-6 px-4 pt-14 pb-28 sm:px-8">
        @unless ($membership->isRenewal())
            <span class="font-mono text-[13px] tracking-wider text-amber-deep">Step 2 of 3</span>
        @endunless
        <h1 class="text-[clamp(36px,4vw,52px)] leading-none font-extrabold tracking-tight">{{ $membership->isRenewal() ? 'Renew your membership' : 'Pay the membership fee' }}</h1>
        <p class="text-lg leading-relaxed text-body">You'll pay securely on SSLCommerz with bKash, Nagad, Rocket or card, then come back here.</p>

        <div class="flex flex-col gap-6 rounded-sm border border-line bg-card p-6 sm:p-10">
            <dl class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 text-[15px]">
                <dt class="text-muted">Membership</dt>
                <dd class="font-semibold">{{ $membership->plan->name }}</dd>
                <dt class="text-muted">Amount</dt>
                <dd class="text-2xl font-extrabold">BDT {{ number_format($membership->amount) }}</dd>
            </dl>
            <form method="POST" action="{{ route('memberships.pay', $membership) }}">
                @csrf
                <x-form.button class="w-full">Pay BDT {{ number_format($membership->amount) }} with SSLCommerz</x-form.button>
            </form>
            <a href="{{ route('portal') }}" class="text-[15px]">Pay later from the members portal</a>
        </div>
    </div>
</x-layouts.site>
