@props(['title', 'active'])

@php
    /** @var \App\Models\User $user */
    $user = auth()->user()->loadMissing(['currentMembership.plan', 'latestMembership.plan']);
    $membership = $user->currentMembership ?? $user->latestMembership;
    $isActive = $membership?->isCurrent() ?? false;

    $items = [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'href' => route('portal'), 'icon' => '<rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>'],
        ['id' => 'directory', 'label' => 'Alumni directory', 'href' => route('directory'), 'icon' => '<circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>'],
        ['id' => 'events', 'label' => 'My events', 'href' => route('portal.events'), 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M3 10h18M8 3v4M16 3v4"></path>'],
        ['id' => 'profile', 'label' => 'My profile', 'href' => route('profile.edit'), 'icon' => '<circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path>'],
    ];
@endphp

<x-layouts.site :title="$title" active="directory">
    <section aria-label="Your membership" class="bg-ink text-paper">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-10 gap-y-5 px-4 py-8 sm:px-8">
            <div class="flex items-center gap-4.5">
                @if ($user->profile?->photoUrl())
                    <img src="{{ $user->profile->photoUrl() }}" alt="" class="size-15 rounded-full object-cover">
                @else
                    <span aria-hidden="true" class="flex size-15 items-center justify-center rounded-full bg-amber text-[22px] font-extrabold text-ink">{{ $user->initials() }}</span>
                @endif
                <div class="flex flex-col gap-1">
                    <x-site.eyebrow class="text-xs!" tone="text-mist-dim">Members portal</x-site.eyebrow>
                    <p class="text-[30px] font-extrabold tracking-tight">Welcome back, {{ $user->name }}</p>
                </div>
            </div>
            @if ($membership)
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px]">
                    <span class="flex items-center gap-2 rounded-full border border-[#3C5F53] px-3.5 py-2">
                        <span aria-hidden="true" @class(['size-2 rounded-full', 'bg-[#7CC4A8]' => $isActive, 'bg-amber' => ! $isActive])></span>
                        {{ $membership->plan->name }} · {{ $membership->status->getLabel() }} · ID {{ $user->memberNumber() }}
                    </span>
                    @if ($isActive)
                        <span class="text-mist-dim">{{ $membership->expires_at ? 'Renews '.$membership->expires_at->format('j M Y') : 'Life membership' }}</span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div class="mx-auto flex max-w-7xl flex-wrap items-start gap-10 px-4 pt-10 pb-28 sm:px-8">
        <nav aria-label="Portal" class="flex flex-[1_1_220px] flex-col gap-0.5 rounded-sm border border-line bg-card p-3">
            @foreach ($items as $item)
                <a href="{{ $item['href'] }}"
                   @if ($active === $item['id']) aria-current="page" @endif
                   @class([
                       'flex min-h-11 items-center gap-3 rounded-xs px-3 text-[15px] no-underline',
                       'bg-paper-dim font-semibold text-ink' => $active === $item['id'],
                       'text-body hover:bg-paper-dim hover:text-ink' => $active !== $item['id'],
                   ])>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-line pt-0.5">
                @csrf
                <button type="submit" class="flex min-h-11 w-full cursor-pointer items-center gap-3 px-3 text-[15px] text-body hover:text-ink">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"></path></svg>
                    Log out
                </button>
            </form>
        </nav>

        <div class="flex min-w-0 flex-[999_1_560px] flex-col gap-10">
            {{ $slot }}
        </div>
    </div>
</x-layouts.site>
