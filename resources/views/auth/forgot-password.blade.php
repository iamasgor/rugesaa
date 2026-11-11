<x-auth-card title="Reset password" heading="Reset your password" intro="Enter your email and we'll send you a reset link.">
    @if (session('status'))
        <p role="status" class="rounded-sm bg-paper-dim px-4 py-3 text-[15px] font-semibold text-forest">{{ session('status') }}</p>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
        @csrf
        <x-form.input name="email" label="Email" type="email" autocomplete="email" required autofocus />
        <x-form.button>Email reset link</x-form.button>
    </form>
    <a href="{{ route('login') }}" class="text-[15px]">← Back to log in</a>
</x-auth-card>
