<x-layouts.site title="Membership" active="membership">
    <x-site.page-hero
        title="Membership"
        breadcrumb="Membership"
        intro="Join the association to unlock the members portal: the alumni directory, your profile and registration for reunions and events.">
        <nav aria-label="On this page" class="flex flex-wrap gap-2.5 pt-3">
            @foreach (['tiers' => 'Membership types', 'how' => 'How to join', 'portal' => 'Members portal', 'questions' => 'Questions'] as $anchor => $label)
                <a href="#{{ $anchor }}" class="flex min-h-10 items-center rounded-full border border-[#6B887C] px-4 text-[15px] text-paper no-underline hover:border-amber hover:text-amber">{{ $label }}</a>
            @endforeach
        </nav>
    </x-site.page-hero>

    <section id="tiers" aria-labelledby="tiers-title" class="mx-auto flex max-w-7xl scroll-mt-6 flex-col gap-10 px-4 pt-24 sm:px-8">
        <div class="flex flex-col gap-3">
            <x-site.eyebrow>Membership types</x-site.eyebrow>
            <h2 id="tiers-title" class="text-[clamp(34px,4vw,52px)] leading-[1.02] font-extrabold tracking-tight">Choose how you belong</h2>
        </div>
        <div class="grid grid-cols-1 items-stretch gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $plan)
                @php($isHighlighted = $plan->isLifetime() && ! $plan->isFree())
                <div @class([
                    'flex flex-col gap-5 rounded-sm p-8',
                    'bg-ink text-paper' => $isHighlighted,
                    'border border-line bg-card' => ! $isHighlighted,
                ])>
                    <div class="flex flex-col gap-2">
                        <x-site.eyebrow class="text-xs!" :tone="$isHighlighted ? 'text-amber' : 'text-muted'">{{ $plan->name }}</x-site.eyebrow>
                        <span class="text-4xl font-extrabold">
                            @if ($plan->isFree())
                                Free
                            @else
                                BDT {{ number_format($plan->price) }}<span @class(['text-base font-medium', 'text-mist-dim' => $isHighlighted, 'text-muted' => ! $isHighlighted])> {{ $plan->isLifetime() ? 'once' : ($plan->duration_months === 12 ? '/ year' : '/ '.$plan->duration_months.' months') }}</span>
                            @endif
                        </span>
                        @if ($plan->description)
                            <span @class(['text-[15px]', 'text-mist-dim' => $isHighlighted, 'text-muted' => ! $isHighlighted])>{{ $plan->description }}</span>
                        @endif
                    </div>
                    @if (filled($plan->benefits))
                        <ul class="flex flex-col gap-3 text-[15px] leading-snug">
                            @foreach ($plan->benefits as $benefit)
                                <li class="flex gap-2.5">
                                    <svg class="mt-0.5 flex-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $isHighlighted ? '#E0A43A' : '#1D4337' }}" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>
                                    {{ $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <a href="{{ route('register', ['plan' => $plan->slug]) }}" @class([
                        'mt-auto flex min-h-12 items-center justify-center rounded-xs text-base no-underline',
                        'bg-amber font-bold text-ink hover:text-ink' => $isHighlighted,
                        'border-[1.5px] border-forest font-semibold text-forest hover:bg-forest hover:text-paper' => ! $isHighlighted,
                    ])>{{ $plan->isFree() ? 'Join as a student' : 'Become a '.Str::lower($plan->name) }}</a>
                </div>
            @endforeach
        </div>
    </section>

    <section id="how" aria-labelledby="how-title" class="mx-auto flex max-w-7xl scroll-mt-6 flex-col gap-8 px-4 pt-24 sm:px-8">
        <div class="flex flex-col gap-3">
            <x-site.eyebrow>How to join</x-site.eyebrow>
            <h2 id="how-title" class="text-[clamp(30px,3.4vw,44px)] leading-[1.05] font-extrabold tracking-tight">Three steps, about ten minutes</h2>
        </div>
        <ol class="grid grid-cols-1 gap-px overflow-hidden rounded-sm border border-line bg-line md:grid-cols-3">
            @foreach ([
                ['Register', 'Fill in your batch, student ID and current work. It takes about five minutes.'],
                ['Pay online', 'bKash, Nagad, Rocket or card through SSLCommerz. Students join free.'],
                ['Get verified', 'The committee checks your batch against department records and emails you when your membership is active.'],
            ] as [$step, $detail])
                <li class="flex flex-col gap-2.5 bg-card p-6">
                    <span class="font-mono text-xs text-amber-deep">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <h3 class="text-[19px] font-bold">{{ $step }}</h3>
                    <p class="text-[15px] leading-normal text-muted">{{ $detail }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section id="portal" aria-labelledby="portal-title" class="mx-auto max-w-7xl scroll-mt-6 px-4 pt-24 sm:px-8">
        <div class="grid grid-cols-1 items-center gap-10 rounded-sm bg-paper-dim p-8 sm:p-12 lg:grid-cols-2">
            <div class="flex flex-col gap-3.5">
                <x-site.eyebrow>Members portal</x-site.eyebrow>
                <h2 id="portal-title" class="text-[clamp(30px,3.4vw,44px)] leading-[1.05] font-extrabold tracking-tight">Everything members use, behind one login</h2>
                <p class="text-[17px] leading-relaxed text-body">Update your profile, search the directory, see your event registrations and check your membership.</p>
                <div class="flex flex-wrap gap-3 pt-2">
                    @auth
                        <a href="{{ route('portal') }}" class="flex min-h-12 items-center rounded-xs bg-forest px-5.5 text-base font-bold text-paper no-underline hover:bg-ink hover:text-paper">Open the portal</a>
                    @else
                        <a href="{{ route('register') }}" class="flex min-h-12 items-center rounded-xs border-[1.5px] border-forest px-5.5 text-base font-semibold text-forest no-underline">Create an account</a>
                    @endauth
                </div>
            </div>
            @guest
                <form method="POST" action="{{ route('login') }}" aria-label="Portal login" class="flex flex-col gap-3.5 rounded-sm border border-line bg-card p-7">
                    @csrf
                    <x-form.input name="email" label="Email" type="email" autocomplete="username" required />
                    <x-form.input name="password" label="Password" type="password" autocomplete="current-password" required />
                    <x-form.button class="min-h-12! text-base!">Log in</x-form.button>
                    <a href="{{ route('password.request') }}" class="text-sm">Forgot password?</a>
                </form>
            @endguest
        </div>
    </section>

    <section id="questions" aria-labelledby="faq-title" class="mx-auto grid max-w-7xl scroll-mt-6 grid-cols-1 gap-x-16 gap-y-8 px-4 pt-24 pb-28 sm:px-8 lg:grid-cols-2">
        <h2 id="faq-title" class="text-[clamp(30px,3.4vw,44px)] leading-[1.05] font-extrabold tracking-tight">Questions</h2>
        <div class="flex flex-col border-t border-line">
            @foreach ([
                ['Who can become a member?', 'Anyone who studied in the Department of Geography & Environmental Studies at the University of Rajshahi at any level. Current students join free as associates.'],
                ['How is my membership verified?', 'The committee checks your batch and student ID against department records. You get an email as soon as your membership is active.'],
                ['How do I pay?', 'Online through SSLCommerz with bKash, Nagad, Rocket or a Visa/Mastercard card. You can also pay later from the members portal.'],
                ['Who can see my details?', 'Only verified members see the directory, and only your name, batch, work and city. Your email, mobile number and student ID are never shown. You can hide your profile at any time.'],
            ] as [$question, $answer])
                <details class="group border-b border-line py-5">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 text-lg font-semibold">
                        {{ $question }}
                        <svg class="flex-none transition-transform group-open:rotate-45" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                    </summary>
                    <p class="pt-3 text-base leading-relaxed text-body">{{ $answer }}</p>
                </details>
            @endforeach
            <p class="pt-6 text-[15px] text-body">Something else? <a href="{{ route('contact') }}">Contact the association</a>.</p>
        </div>
    </section>
</x-layouts.site>
