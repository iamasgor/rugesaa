@php
    $selectedFund = old('fund', $funds[0]->value);
    $selectedAmount = (string) old('amount', $presetAmounts[1]);
@endphp

<x-layouts.site title="Donation" active="donate">
    <x-site.page-hero
        title="Donation"
        breadcrumb="Donation"
        intro="Support students, fieldwork and the department's labs. Give once to the fund you care about — online with bKash, Nagad, Rocket or card." />

    <section id="give" aria-labelledby="give-title" class="mx-auto grid max-w-7xl grid-cols-1 gap-12 px-4 pt-24 pb-28 sm:px-8 lg:grid-cols-2">
        <div class="flex flex-col gap-4.5">
            <x-site.eyebrow>Donations &amp; funding</x-site.eyebrow>
            <h2 id="give-title" class="text-[clamp(34px,4vw,56px)] leading-none font-extrabold tracking-tight">Give to the fund you care about</h2>
            <p class="text-[17px] leading-relaxed text-body">Every gift is receipted by email right after the payment goes through. You don't need an account to give.</p>

            <ul class="flex flex-col gap-3">
                @foreach ($funds as $fund)
                    <li class="flex flex-col gap-1 border-b border-line pb-3">
                        <span class="text-[17px] font-bold">{{ $fund->getLabel() }}</span>
                        <span class="text-[15px] text-muted">{{ $fund->description() }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="mt-2 flex flex-col gap-3 rounded-sm border border-line bg-card p-6">
                <h3 class="text-[19px] font-bold">Other ways to give</h3>
                <p class="text-[15px] leading-relaxed text-muted">Prefer a bank transfer, want to set up a named scholarship in memory of a teacher, or give equipment for the GIS lab? Get in touch and the treasurer will help.</p>
                <a href="{{ route('contact') }}" class="text-[15px] font-semibold">Talk to the treasurer →</a>
            </div>
        </div>

        <form method="POST" action="{{ route('donate.store') }}" aria-labelledby="donate-title" class="flex flex-col gap-6 self-start rounded-sm border border-line bg-card p-6 sm:p-8">
            @csrf
            <h3 id="donate-title" class="text-2xl font-extrabold">Make a gift</h3>

            <fieldset class="flex flex-col gap-2.5">
                <legend class="pb-2.5 text-sm font-semibold">1. Choose a fund</legend>
                @foreach ($funds as $fund)
                    <label class="flex cursor-pointer flex-col items-start gap-1 rounded-sm border border-[#9AA8A0] bg-white px-4 py-3.5 has-checked:border-forest has-checked:bg-paper-dim has-checked:shadow-[inset_0_0_0_1px_var(--color-forest)] has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-forest">
                        <input type="radio" name="fund" value="{{ $fund->value }}" @checked($selectedFund === $fund->value) class="sr-only">
                        <span class="text-base font-bold">{{ $fund->getLabel() }}</span>
                        <span class="text-sm text-muted">{{ $fund->description() }}</span>
                    </label>
                @endforeach
                @error('fund')
                    <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="flex flex-col gap-2.5">
                <legend class="pb-2.5 text-sm font-semibold">2. Amount (BDT)</legend>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                    @foreach ([...$presetAmounts, 'other'] as $amount)
                        <label class="flex min-h-12 cursor-pointer items-center justify-center rounded-xs border border-[#9AA8A0] bg-white text-base font-semibold has-checked:border-forest has-checked:bg-forest has-checked:font-bold has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-forest">
                            <input type="radio" name="amount" value="{{ $amount }}" @checked($selectedAmount === (string) $amount) class="sr-only">
                            {{ $amount === 'other' ? 'Other' : number_format($amount) }}
                        </label>
                    @endforeach
                </div>
                @error('amount')
                    <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
                @enderror
                <x-form.input name="other_amount" label="Other amount" :hint="'(BDT '.number_format($minimumAmount).'–'.number_format($maximumAmount).', choose “Other” above)'" type="number" inputmode="numeric" :min="$minimumAmount" :max="$maximumAmount" step="1" />
            </fieldset>

            <fieldset class="flex flex-col gap-4">
                <legend class="pb-2.5 text-sm font-semibold">3. Your details</legend>
                <x-form.input name="name" label="Full name *" autocomplete="name" :value="$donor['name']" required />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="email" label="Email *" hint="(for your receipt)" type="email" autocomplete="email" :value="$donor['email']" required />
                    <x-form.input name="phone" label="Mobile *" type="tel" autocomplete="tel" :value="$donor['phone']" required />
                </div>
            </fieldset>

            <button type="submit" class="flex min-h-14 cursor-pointer items-center justify-center gap-2.5 rounded-xs bg-amber px-6 text-[17px] font-extrabold text-ink hover:bg-[#d0952c]">
                Give now
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
            </button>
            <p class="text-[13px] leading-normal text-muted">You'll pay with bKash, Nagad, Rocket or card on the secure SSLCommerz page. A receipt is emailed right after.</p>
        </form>
    </section>
</x-layouts.site>
