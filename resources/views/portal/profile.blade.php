@php
    use App\Enums\Sector;
    use App\Enums\Specialization;

    $optionsFor = fn (string $enum): array => collect($enum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->all();
@endphp

<x-portal.shell title="My profile" active="profile">
    <section aria-labelledby="profile-title" class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <h1 id="profile-title" class="text-[clamp(30px,3.4vw,42px)] leading-[1.05] font-extrabold tracking-tight">My profile</h1>
            <p class="text-base text-muted">This is what other verified members see in the directory. Your email, mobile number and student ID are never shown there.</p>
        </div>

        @if (session('profile_saved'))
            <p role="status" class="rounded-sm border border-forest bg-paper-dim px-5 py-4 text-[15px] font-semibold text-forest">Profile saved.</p>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-10 rounded-sm border border-line bg-card p-6 sm:p-10">
            @csrf
            @method('PUT')

            <fieldset class="flex flex-col gap-4.5">
                <legend class="pb-4.5 text-[22px] font-bold">Verified record</legend>
                <dl class="grid grid-cols-1 gap-4 text-[15px] sm:grid-cols-3">
                    <div class="flex flex-col gap-1"><dt class="text-muted">Name</dt><dd class="font-semibold">{{ $user->name }}</dd></div>
                    <div class="flex flex-col gap-1"><dt class="text-muted">Batch</dt><dd class="font-semibold">{{ $profile->batch }}</dd></div>
                    <div class="flex flex-col gap-1"><dt class="text-muted">Degree</dt><dd class="font-semibold">{{ $profile->degree->getLabel() }}</dd></div>
                </dl>
                <p class="text-sm text-muted">These were checked against department records. To correct them, <a href="{{ route('contact') }}">contact the association</a>.</p>
            </fieldset>

            <fieldset class="flex flex-col gap-4.5">
                <legend class="pb-4.5 text-[22px] font-bold">Contact &amp; photo</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="phone" label="Mobile number" type="tel" autocomplete="tel" :value="$profile->phone" required />
                    <x-form.input name="photo" label="Profile photo" hint="(max 2 MB)" type="file" accept="image/*" />
                </div>
                @if ($profile->photoUrl())
                    <div class="flex items-center gap-4">
                        <img src="{{ $profile->photoUrl() }}" alt="Your current photo" class="size-16 rounded-full object-cover">
                        <x-form.checkbox name="remove_photo">Remove current photo</x-form.checkbox>
                    </div>
                @endif
            </fieldset>

            <fieldset class="flex flex-col gap-4.5">
                <legend class="pb-4.5 text-[22px] font-bold">Work &amp; study</legend>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-form.input name="position" label="Current position" autocomplete="organization-title" :value="$profile->position" />
                    <x-form.input name="organization" label="Organization" autocomplete="organization" :value="$profile->organization" />
                    <x-form.select name="sector" label="Sector" :options="$optionsFor(Sector::class)" :value="$profile->sector->value" />
                    <x-form.input name="location" label="City & country" :value="$profile->location" />
                    <x-form.select name="specialization" label="Specialization / thesis area" :options="$optionsFor(Specialization::class)" :value="$profile->specialization->value" />
                    <x-form.input name="hall" label="Hall of residence" :value="$profile->hall" />
                </div>
            </fieldset>

            <fieldset class="flex flex-col gap-1">
                <legend class="pb-3.5 text-[22px] font-bold">Privacy &amp; preferences</legend>
                <x-form.checkbox name="show_in_directory" :checked="$profile->show_in_directory">Show my profile in the alumni directory</x-form.checkbox>
                <x-form.checkbox name="mentor_opt_in" :checked="$profile->mentor_opt_in">Show me as open to mentoring students and junior graduates</x-form.checkbox>
                <x-form.checkbox name="newsletter_opt_in" :checked="$profile->newsletter_opt_in">Send me the alumni newsletter and event invitations</x-form.checkbox>
            </fieldset>

            <div class="flex justify-end border-t border-line pt-6">
                <x-form.button>Save profile</x-form.button>
            </div>
        </form>
    </section>

    <section aria-labelledby="password-title" class="flex flex-col gap-6">
        <h2 id="password-title" class="text-[28px] font-extrabold">Change password</h2>
        @if (session('status') === 'password-updated')
            <p role="status" class="rounded-sm border border-forest bg-paper-dim px-5 py-4 text-[15px] font-semibold text-forest">Password changed.</p>
        @endif
        <form method="POST" action="{{ route('user-password.update') }}" class="flex flex-col gap-5 rounded-sm border border-line bg-card p-6 sm:p-10">
            @csrf
            @method('PUT')
            @php($passwordErrors = $errors->getBag('updatePassword'))
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
                    <div class="flex flex-col gap-1.5">
                        <label for="{{ $field }}" class="text-sm font-semibold">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}" required class="min-h-12 rounded-xs border border-line-strong bg-white px-3.5 text-base">
                        @if ($passwordErrors->has($field))
                            <p class="text-sm font-semibold text-amber-deep">{{ $passwordErrors->first($field) }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end">
                <x-form.button>Change password</x-form.button>
            </div>
        </form>
    </section>
</x-portal.shell>
