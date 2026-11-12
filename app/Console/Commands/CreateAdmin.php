<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;

#[Signature('app:create-admin
    {email : Email address the admin will log in with}
    {--name= : Display name (defaults to "RUGESAA Admin" for new users)}
    {--ask-password : Type the password yourself instead of generating one}')]
#[Description('Create an admin panel user, or promote an existing user and reset their password')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->components->error("\"{$email}\" is not a valid email address.");

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user !== null && ! confirm(
            label: "A user with {$email} already exists. Make them an admin and reset their password?",
            default: false,
        )) {
            $this->components->warn('Nothing changed.');

            return self::FAILURE;
        }

        $isPasswordGenerated = ! $this->option('ask-password');
        $plainPassword = $isPasswordGenerated ? Str::password(24) : $this->askForPassword();

        $user ??= new User(['email' => $email]);
        $name = $this->option('name');

        $user->forceFill([
            'name' => is_string($name) && $name !== '' ? $name : ($user->name ?? 'RUGESAA Admin'),
            'password' => Hash::make($plainPassword),
            'is_admin' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->components->info("{$email} can now log in at ".url('/admin'));

        if ($isPasswordGenerated) {
            $this->components->twoColumnDetail('Password', $plainPassword);
            $this->components->warn('Save this password now — it will not be shown again.');
        }

        return self::SUCCESS;
    }

    private function askForPassword(): string
    {
        return password(
            label: 'Password',
            required: true,
            validate: fn (string $value): ?string => Validator::make(
                ['password' => $value],
                ['password' => [Password::defaults()]],
            )->errors()->first('password') ?: null,
        );
    }
}
