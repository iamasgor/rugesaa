<x-auth-card title="Log in" heading="Members portal" intro="Log in with the email you registered with.">
    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
        @csrf
        <x-form.input name="email" label="Email" type="email" autocomplete="email" required autofocus />
        <x-form.input name="password" label="Password" type="password" autocomplete="current-password" required />
        <x-form.checkbox name="remember">Keep me logged in</x-form.checkbox>
        <x-form.button>Log in</x-form.button>
    </form>
    <div class="flex flex-wrap justify-between gap-3 text-[15px]">
        <a href="{{ route('password.request') }}">Forgot your password?</a>
        <a href="{{ route('register') }}" class="font-semibold">Not a member yet? Register →</a>
    </div>
</x-auth-card>
