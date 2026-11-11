@php
    use App\Enums\Degree;
    use App\Enums\Sector;
    use App\Enums\Specialization;

    $optionsFor = fn (string $enum): array => collect($enum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->all();
    $requestedPlan = $plans->firstWhere('slug', request()->query('plan'));
    $defaultPlanId = old('membership_plan_id', $requestedPlan?->id ?? $plans->firstWhere('slug', 'general')?->id ?? $plans->first()?->id);
@endphp

<x-layouts.site title="Registration" active="register">
    <div class="mx-auto flex max-w-7xl flex-wrap items-start gap-x-16 gap-y-12 px-4 pt-14 pb-28 sm:px-8">
        <aside class="flex flex-[1_1_320px] flex-col gap-7">
            <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 font-mono text-[13px] tracking-wider text-muted">
                <a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span>Registration</span>
            </nav>
            <h1 class="text-[clamp(42px,4.6vw,64px)] leading-[0.98] font-extrabold tracking-tight">Join the alumni network</h1>
            <p class="text-lg leading-relaxed text-body">Register once. After we verify your batch with department records, you'll get access to the members portal.</p>
            <ol class="flex flex-col border-l-2 border-line">
                @foreach ([['Fill in the form', 'About five minutes.'], ['Pay the membership fee', 'bKash, Nagad, Rocket or card through SSLCommerz. Students join free.'], ['Get verified', 'The committee checks your batch and emails you.']] as $index => [$step, $detail])
                    <li class="relative flex flex-col gap-1 pl-6 {{ $loop->last ? '' : 'pb-5.5' }}">
                        <span aria-hidden="true" class="absolute top-0.5 -left-[9px] size-4 rounded-full border-2 border-forest {{ $index === 0 ? 'bg-amber' : 'bg-paper' }}"></span>
                        <span class="text-base font-bold">{{ $step }}</span>
                        <span class="text-[15px] text-muted">{{ $detail }}</span>
                    </li>
                @endforeach
            </ol>
            <div class="flex flex-col gap-2 rounded-sm bg-paper-dim p-5 text-[15px] leading-normal">
                <span class="font-bold">Already registered?</span>
                <a href="{{ route('login') }}" class="font-semibold">Log in to the members portal →</a>
            </div>
        </aside>

        <div class="min-w-0 flex-[999_1_560px]">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" aria-label="Alumni registration" class="flex flex-col gap-10 rounded-sm border border-line bg-card p-6 sm:p-10">
                @csrf

                @if ($errors->any())
                    <div role="alert" class="rounded-sm border border-amber-deep bg-white px-5 py-4 text-[15px] text-amber-deep">
                        <strong>Please fix {{ $errors->count() === 1 ? 'the field' : $errors->count().' fields' }} marked below.</strong>
                    </div>
                @endif

                <fieldset class="flex flex-col gap-4.5">
                    <legend class="flex items-baseline gap-3 pb-4.5"><span class="font-mono text-[13px] text-amber-deep">01</span><span class="text-[22px] font-bold">Personal details</span></legend>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.input name="name" label="Full name" autocomplete="name" required />
                        <x-form.input name="email" label="Email" type="email" autocomplete="email" required />
                        <x-form.input name="phone" label="Mobile number" type="tel" autocomplete="tel" placeholder="+880" required />
                        <x-form.input name="photo" label="Profile photo" hint="(optional, max 2 MB)" type="file" accept="image/*" />
                        <x-form.input name="password" label="Password" type="password" autocomplete="new-password" required />
                        <x-form.input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />
                    </div>
                </fieldset>

                <fieldset class="flex flex-col gap-4.5">
                    <legend class="flex items-baseline gap-3 pb-4.5"><span class="font-mono text-[13px] text-amber-deep">02</span><span class="text-[22px] font-bold">Academic record</span></legend>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.select name="degree" label="Highest degree from the department" :options="$optionsFor(Degree::class)" />
                        <x-form.input name="batch" label="Batch / session" placeholder="e.g. 2008–09" required />
                        <x-form.input name="student_id" label="Student ID / registration no." required />
                        <x-form.input name="hall" label="Hall of residence" />
                    </div>
                    <x-form.select name="specialization" label="Specialization / thesis area" :options="$optionsFor(Specialization::class)" />
                </fieldset>

                <fieldset class="flex flex-col gap-4.5">
                    <legend class="flex items-baseline gap-3 pb-4.5"><span class="font-mono text-[13px] text-amber-deep">03</span><span class="text-[22px] font-bold">Work &amp; location</span></legend>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-form.input name="position" label="Current position" autocomplete="organization-title" />
                        <x-form.input name="organization" label="Organization" autocomplete="organization" />
                        <x-form.select name="sector" label="Sector" :options="$optionsFor(Sector::class)" />
                        <x-form.input name="location" label="City & country" />
                    </div>
                </fieldset>

                <fieldset class="flex flex-col gap-3.5">
                    <legend class="flex items-baseline gap-3 pb-4.5"><span class="font-mono text-[13px] text-amber-deep">04</span><span class="text-[22px] font-bold">Membership type</span></legend>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        @foreach ($plans as $plan)
                            <label class="flex cursor-pointer flex-col gap-1.5 rounded-sm border border-line-strong bg-white p-4.5 has-checked:border-2 has-checked:border-forest has-checked:bg-paper-dim">
                                <span class="flex items-center gap-2.5 text-base font-bold">
                                    <input type="radio" name="membership_plan_id" value="{{ $plan->id }}" @checked((string) $defaultPlanId === (string) $plan->id) class="size-4.5 accent-forest">
                                    {{ $plan->name }}
                                </span>
                                <span class="pl-7 text-[15px] text-body">{{ $plan->formattedPrice() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('membership_plan_id')
                        <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
                    @enderror
                    <div class="flex flex-col pt-2.5">
                        <x-form.checkbox name="mentor_opt_in">I'd like to mentor students or junior graduates</x-form.checkbox>
                        <x-form.checkbox name="show_in_directory" :checked="true">Show my profile in the alumni directory</x-form.checkbox>
                        <x-form.checkbox name="newsletter_opt_in" :checked="true">Send me the alumni newsletter and event invitations</x-form.checkbox>
                    </div>
                </fieldset>

                <div class="flex flex-col gap-4 border-t border-line pt-6">
                    <x-form.checkbox name="terms">I agree to the association's <a href="{{ route('about') }}#documents">constitution</a> and to my details being checked against department records.</x-form.checkbox>
                    <div class="flex justify-end">
                        <x-form.button>
                            Submit registration
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        </x-form.button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.site>
