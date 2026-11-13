@php
    use App\Enums\ContactTopic;

    $department = config('rugesaa.department');
    $coordinates = $department['latitude'].','.$department['longitude'];
    $selectedTopic = old('topic', ContactTopic::General->value);
    $icon = 'flex-none mt-0.5';
@endphp

<x-layouts.site title="Contact" active="contact">
    <x-site.page-hero
        title="Contact us"
        breadcrumb="Contact"
        intro="Questions about membership, reunions or your directory profile? Write to us, call the department office, or drop by on campus." />

    <section aria-label="Location map" class="relative min-h-[560px] overflow-hidden border-b border-line bg-paper-dim">
        <iframe
            src="https://maps.google.com/maps?q={{ $coordinates }}&amp;z=14&amp;hl=en&amp;output=embed"
            title="Google Map: Department of Geography and Environmental Studies, University of Rajshahi"
            class="absolute inset-0 size-full border-0 grayscale transition-[filter] duration-300 hover:grayscale-0"
            allowfullscreen
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"></iframe>
        <div class="pointer-events-none relative mx-auto flex min-h-[560px] max-w-7xl items-end px-4 py-12 sm:px-8">
            <div class="pointer-events-auto flex w-full max-w-md flex-col gap-3.5 rounded-sm border border-line bg-card p-7 shadow-[0_18px_40px_rgba(20,35,30,0.14)]">
                <x-site.eyebrow class="text-xs!">Find us</x-site.eyebrow>
                <h2 class="text-2xl leading-tight font-extrabold">Ground Floor, Sir Jagadish Chandra Bose Academic Building</h2>
                <p class="text-base leading-normal text-body">University of Rajshahi, Rajshahi 6205, Bangladesh</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1.5 font-mono text-[13px] text-muted">
                    <span>{{ round((float) $department['latitude'], 4) }}° N, {{ round((float) $department['longitude'], 4) }}° E</span>
                    <span>Plus code 9JCP+JH2</span>
                </div>
                <div class="flex flex-wrap gap-2.5 pt-1.5">
                    <a href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $coordinates }}" rel="noopener" class="flex min-h-12 items-center gap-2 rounded-xs bg-forest px-5 text-[15px] font-bold text-paper no-underline hover:bg-ink hover:text-paper">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"></path><circle cx="12" cy="9.5" r="2.5"></circle></svg>
                        Get directions
                    </a>
                    <a href="https://www.google.com/maps?q={{ $coordinates }}" rel="noopener" class="flex min-h-12 items-center rounded-xs border-[1.5px] border-forest px-5 text-[15px] font-semibold text-forest no-underline">Open in Google Maps</a>
                </div>
            </div>
        </div>
    </section>

    <section aria-label="Contact details and form" class="mx-auto flex max-w-7xl flex-wrap items-start gap-x-16 gap-y-12 px-4 pt-22 pb-28 sm:px-8">
        <div class="flex flex-[1_1_340px] flex-col gap-7">
            <div class="flex flex-col gap-3">
                <x-site.eyebrow>Contact information</x-site.eyebrow>
                <h2 class="text-[clamp(30px,3.4vw,42px)] leading-[1.05] font-extrabold tracking-tight">Reach the right person</h2>
            </div>

            <div class="flex flex-col gap-2.5 rounded-sm bg-ink p-6 text-paper">
                <x-site.eyebrow class="text-xs!" tone="text-amber">Department office</x-site.eyebrow>
                <span class="text-lg font-bold">{{ $department['chairman'] }}</span>
                <span class="text-[15px] text-mist-dim">Professor &amp; Chairman, Department of Geography &amp; Environmental Studies</span>
                <ul class="flex flex-col gap-2.5 pt-1.5 text-[15px] leading-normal">
                    <li class="flex gap-2.5">
                        <svg class="{{ $icon }}" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E0A43A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                        <a href="mailto:{{ $department['email'] }}" class="text-paper">{{ $department['email'] }}</a>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="{{ $icon }}" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E0A43A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"></path></svg>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $department['phone']) }}" class="text-paper">{{ $department['phone'] }} (phone &amp; fax)</a>
                    </li>
                    <li class="flex gap-2.5">
                        <svg class="{{ $icon }}" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E0A43A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"></path><circle cx="12" cy="9.5" r="2.5"></circle></svg>
                        <span class="text-mist">{{ $department['address'] }}</span>
                    </li>
                </ul>
            </div>

            @if (config('rugesaa.email') || config('rugesaa.phone'))
                <div class="flex flex-col gap-2.5 rounded-sm border border-line bg-card p-6">
                    <x-site.eyebrow class="text-xs!">Alumni association</x-site.eyebrow>
                    <span class="text-lg font-bold">RUGESAA secretariat</span>
                    <span class="text-[15px] leading-normal text-muted">Membership, reunions and the directory.</span>
                    @if (config('rugesaa.email'))
                        <a href="mailto:{{ config('rugesaa.email') }}" class="text-[15px] font-semibold">{{ config('rugesaa.email') }}</a>
                    @endif
                    @if (config('rugesaa.phone'))
                        <span class="text-[15px] text-body">{{ config('rugesaa.phone') }}</span>
                    @endif
                </div>
            @endif

            @if (config('rugesaa.office_hours'))
                <div class="flex flex-col gap-2 px-1">
                    <x-site.eyebrow class="text-xs!">Office hours</x-site.eyebrow>
                    <span class="text-base font-bold">{{ config('rugesaa.office_hours') }}</span>
                </div>
            @endif

            <x-site.social-links />
        </div>

        <div class="min-w-0 flex-[999_1_520px]">
            @if (session('contact_sent'))
                <div role="status" class="flex flex-col gap-4 rounded-sm bg-ink p-8 text-paper sm:p-12">
                    <span aria-hidden="true" class="flex size-14 items-center justify-center rounded-full bg-amber text-ink">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                    </span>
                    <h2 class="text-[34px] font-extrabold">Message sent</h2>
                    <p class="text-[17px] leading-relaxed text-mist">Thanks for writing about <strong>{{ session('contact_sent') }}</strong>. We'll reply by email.</p>
                    <a href="{{ route('contact') }}" class="flex min-h-12 items-center self-start rounded-xs border-[1.5px] border-mist-dim px-5.5 text-base font-semibold text-paper no-underline hover:text-amber">Send another message</a>
                </div>
            @else
                <form method="POST" action="{{ route('contact.store') }}" aria-labelledby="form-title" class="flex flex-col gap-7 rounded-sm border border-line bg-card p-6 sm:p-10">
                    @csrf
                    <div class="flex flex-col gap-1.5">
                        <h2 id="form-title" class="text-[28px] font-extrabold">Send us a message</h2>
                        <p class="text-[15px] text-muted">Fields marked * are required.</p>
                    </div>
                    <fieldset class="flex flex-col gap-3">
                        <legend class="pb-3 text-sm font-semibold">What is it about? *</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($topics as $topic)
                                <label class="flex min-h-11 cursor-pointer items-center rounded-full border-[1.5px] border-line-strong bg-white px-4 text-sm font-medium has-checked:border-forest has-checked:bg-forest has-checked:font-semibold has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-forest">
                                    <input type="radio" name="topic" value="{{ $topic->value }}" @checked($selectedTopic === $topic->value) class="sr-only">
                                    {{ $topic->getLabel() }}
                                </label>
                            @endforeach
                        </div>
                        @error('topic')
                            <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
                        @enderror
                    </fieldset>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.input name="name" label="Full name *" autocomplete="name" required />
                        <x-form.input name="email" label="Email *" type="email" autocomplete="email" required />
                        <x-form.input name="phone" label="Mobile" hint="(optional)" type="tel" autocomplete="tel" placeholder="+880" />
                        <x-form.input name="batch" label="Batch / session" hint="(optional)" placeholder="e.g. 2008–09" />
                    </div>
                    <x-form.input name="subject" label="Subject *" required />
                    <x-form.textarea name="message" label="Message *" :rows="7" required />
                    <x-form.checkbox name="newsletter_opt_in">Send me the alumni newsletter too</x-form.checkbox>
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-line pt-6">
                        <span class="text-sm text-muted">We only use your details to reply to you.</span>
                        <x-form.button>
                            Send message
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        </x-form.button>
                    </div>
                </form>
            @endif
        </div>
    </section>
</x-layouts.site>
