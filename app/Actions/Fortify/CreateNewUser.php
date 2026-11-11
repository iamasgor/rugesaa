<?php

namespace App\Actions\Fortify;

use App\Enums\Degree;
use App\Enums\MembershipStatus;
use App\Enums\Sector;
use App\Enums\Specialization;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and register a new alumni member with their profile and chosen membership.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s\-]{8,20}$/'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'degree' => ['required', Rule::enum(Degree::class)],
            'batch' => ['required', 'string', 'max:20'],
            'student_id' => ['required', 'string', 'max:50'],
            'hall' => ['nullable', 'string', 'max:255'],
            'specialization' => ['required', Rule::enum(Specialization::class)],
            'position' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'sector' => ['required', Rule::enum(Sector::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'membership_plan_id' => ['required', Rule::exists(MembershipPlan::class, 'id')->where('is_active', true)],
            'mentor_opt_in' => ['boolean'],
            'show_in_directory' => ['boolean'],
            'newsletter_opt_in' => ['boolean'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Please accept the constitution and record check to continue.',
            'phone.regex' => 'Enter a valid mobile number, for example +8801712345678.',
        ])->validate();

        $plan = MembershipPlan::query()->whereKey($validated['membership_plan_id'])->firstOrFail();

        $photo = $validated['photo'] ?? null;
        $photoPath = $photo instanceof UploadedFile ? $photo->store('profile-photos', 'public') : null;

        return DB::transaction(function () use ($validated, $plan, $photoPath): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->profile()->create([
                'phone' => $validated['phone'],
                'photo_path' => $photoPath,
                'degree' => $validated['degree'],
                'batch' => $validated['batch'],
                'student_id' => $validated['student_id'],
                'hall' => $validated['hall'] ?? null,
                'specialization' => $validated['specialization'],
                'position' => $validated['position'] ?? null,
                'organization' => $validated['organization'] ?? null,
                'sector' => $validated['sector'],
                'location' => $validated['location'] ?? null,
                'mentor_opt_in' => (bool) ($validated['mentor_opt_in'] ?? false),
                'show_in_directory' => (bool) ($validated['show_in_directory'] ?? false),
                'newsletter_opt_in' => (bool) ($validated['newsletter_opt_in'] ?? false),
            ]);

            $user->memberships()->create([
                'membership_plan_id' => $plan->id,
                'amount' => $plan->price,
                'status' => $plan->isFree() ? MembershipStatus::PendingVerification : MembershipStatus::PendingPayment,
            ]);

            return $user;
        });
    }
}
