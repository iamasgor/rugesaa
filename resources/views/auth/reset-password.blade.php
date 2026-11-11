<x-auth-card title="Choose a new password" heading="Choose a new password">
    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-form.input name="email" label="Email" type="email" autocomplete="email" :value="$request->email" required />
        <x-form.input name="password" label="New password" type="password" autocomplete="new-password" required autofocus />
        <x-form.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
        <x-form.button>Save password</x-form.button>
    </form>
</x-auth-card>
