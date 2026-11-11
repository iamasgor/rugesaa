<x-auth-card title="Verify your email" heading="Check your email" intro="We sent a verification link to your email address. Click it to open the members portal.">
    @if (session('status') === 'verification-link-sent')
        <p role="status" class="rounded-sm bg-paper-dim px-4 py-3 text-[15px] font-semibold text-forest">A new verification link has been sent.</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-form.button>Resend verification email</x-form.button>
    </form>
</x-auth-card>
