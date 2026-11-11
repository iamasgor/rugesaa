@php
    use App\Enums\MembershipStatus;
@endphp

<x-portal.shell title="Members portal" active="dashboard">
    <section aria-labelledby="membership-title" class="flex flex-col gap-5 rounded-sm border border-line bg-card p-6 sm:p-10">
        <h1 id="membership-title" class="text-[clamp(28px,3vw,36px)] font-extrabold tracking-tight">Your membership</h1>

        @if ($membership)
            <dl class="grid grid-cols-1 gap-x-10 gap-y-4 text-[15px] sm:grid-cols-3">
                <div class="flex flex-col gap-1">
                    <dt class="text-muted">Plan</dt>
                    <dd class="font-semibold">{{ $membership->plan->name }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-muted">Status</dt>
                    <dd class="font-semibold">{{ $membership->status->getLabel() }}</dd>
                </div>
                <div class="flex flex-col gap-1">
                    <dt class="text-muted">{{ $membership->status === MembershipStatus::Expired ? 'Ended' : 'Valid until' }}</dt>
                    <dd class="font-semibold">
                        @if (in_array($membership->status, [MembershipStatus::Active, MembershipStatus::Expired], true))
                            {{ $membership->expires_at?->format('j M Y') ?? 'Lifetime' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($pending?->needsPayment())
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-sm bg-paper-dim p-5">
                    <p class="text-[15px]">Your {{ $pending->isRenewal() ? 'renewal' : 'fee' }} of <strong>BDT {{ number_format($pending->amount) }}</strong> ({{ $pending->plan->name }}) is still due.</p>
                    <a href="{{ route('memberships.checkout', $pending) }}" class="flex min-h-12 items-center rounded-xs bg-amber px-5 font-bold text-ink no-underline hover:text-ink">Pay now</a>
                </div>
            @endif

            @switch($membership->status)
                @case(MembershipStatus::PendingPayment)
                    @unless ($pending)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-sm bg-paper-dim p-5">
                            <p class="text-[15px]">Your fee of <strong>BDT {{ number_format($membership->amount) }}</strong> is still due.</p>
                            <a href="{{ route('memberships.checkout', $membership) }}" class="flex min-h-12 items-center rounded-xs bg-amber px-5 font-bold text-ink no-underline hover:text-ink">Pay now</a>
                        </div>
                    @endunless
                    @break
                @case(MembershipStatus::PendingVerification)
                    <p class="rounded-sm bg-paper-dim p-5 text-[15px]">The committee is checking your batch against department records. We'll email you once it's done. The alumni directory opens after that.</p>
                    @break
                @case(MembershipStatus::Rejected)
                    <p class="rounded-sm bg-paper-dim p-5 text-[15px]">We couldn't verify your registration{{ $membership->rejection_reason ? ': '.$membership->rejection_reason : '.' }} Please <a href="{{ route('contact') }}">contact the association</a>.</p>
                    @break
                @case(MembershipStatus::Active)
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('directory') }}" class="flex min-h-12 items-center rounded-xs bg-forest px-5 font-bold text-paper no-underline hover:bg-ink hover:text-paper">Search the alumni directory</a>
                        <a href="{{ route('profile.edit') }}" class="flex min-h-12 items-center rounded-xs border-[1.5px] border-forest px-5 font-semibold text-forest no-underline">Update my profile</a>
                    </div>
                    @break
            @endswitch
        @else
            <p class="text-[15px]">You don't have a membership yet.</p>
        @endif
    </section>

    @if ($canRenew)
        <section aria-labelledby="renew-title" class="flex flex-col gap-5 rounded-sm border-2 border-amber bg-card p-6 sm:p-10">
            <div class="flex flex-col gap-2">
                <h2 id="renew-title" class="text-2xl font-extrabold">{{ $membership?->status === MembershipStatus::Expired ? 'Renew your membership' : 'Your membership ends soon' }}</h2>
                <p class="text-[15px] text-body">
                    You were verified before, so a renewal is active as soon as it's paid.
                    @if ($membership?->isCurrent())
                        Renewing early loses no time — the new period starts on {{ $membership->expires_at->format('j M Y') }}.
                    @endif
                    You can also switch plan, for example from student to general, or to life membership.
                </p>
            </div>
            <form method="POST" action="{{ route('memberships.renew') }}" class="flex flex-col gap-4">
                @csrf
                <fieldset class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <legend class="sr-only">Membership type</legend>
                    @foreach ($renewalPlans as $plan)
                        <label class="flex cursor-pointer flex-col gap-1.5 rounded-sm border border-line-strong bg-white p-4.5 has-checked:border-2 has-checked:border-forest has-checked:bg-paper-dim">
                            <span class="flex items-center gap-2.5 text-base font-bold">
                                <input type="radio" name="membership_plan_id" value="{{ $plan->id }}" @checked(old('membership_plan_id', $membership?->membership_plan_id) == $plan->id) required class="size-4.5 accent-forest">
                                {{ $plan->name }}
                            </span>
                            <span class="pl-7 text-[15px] text-body">{{ $plan->formattedPrice() }}</span>
                        </label>
                    @endforeach
                </fieldset>
                @error('membership_plan_id')
                    <p class="text-sm font-semibold text-amber-deep">{{ $message }}</p>
                @enderror
                <div>
                    <x-form.button>Renew membership</x-form.button>
                </div>
            </form>
        </section>
    @endif
</x-portal.shell>
