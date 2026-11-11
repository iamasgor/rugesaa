@props(['active' => null])

@php

    $navigation = [
        ['id' => 'home', 'label' => 'Home', 'href' => route('home')],
        ['id' => 'about', 'label' => 'About', 'href' => route('about')],
        ['id' => 'events', 'label' => 'Events & Reunions', 'href' => route('events.index')],
        ['id' => 'membership', 'label' => 'Membership', 'href' => route('membership')],
        ['id' => 'directory', 'label' => 'Directory', 'href' => route('directory')],
        ['id' => 'news', 'label' => 'News', 'href' => route('news.index')],
    ];
@endphp

<header class="border-b border-line bg-paper text-ink">
    <div class="bg-forest-deep text-mist">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-1.5 text-[13px] sm:px-8">
            <span class="font-mono tracking-wide">Department of Geography &amp; Environmental Studies · University of Rajshahi</span>
            <nav aria-label="Utility" class="flex flex-wrap items-center gap-x-5">
                @auth
                    <a href="{{ route('portal') }}" class="py-2 text-paper no-underline hover:text-amber">Members Portal</a>
                    @if (auth()->user()->is_admin)
                        <a href="{{ url('/admin') }}" class="py-2 text-paper no-underline hover:text-amber">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="cursor-pointer py-2 text-paper hover:text-amber">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-1.5 py-2 text-paper no-underline hover:text-amber">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
                        Members Portal login
                    </a>
                @endauth
                <a href="{{ route('contact') }}" class="py-2 text-paper no-underline hover:text-amber">Contact</a>
            </nav>
        </div>
    </div>
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-8 gap-y-3 px-4 py-3.5 sm:px-8">
        <a href="{{ route('home') }}" class="flex items-center no-underline">
            <img src="{{ asset('images/rugesaa-logo.png') }}" alt="RUGESAA — Rajshahi University Geography &amp; Environmental Studies Alumni Association" width="788" height="317" class="h-14 w-auto sm:h-16">
        </a>
        <nav aria-label="Main" class="flex flex-wrap items-center gap-0.5">
            @foreach ($navigation as $item)
                <a href="{{ $item['href'] }}"
                   @if ($active === $item['id']) aria-current="page" @endif
                   @class([
                       'block px-2.5 py-3 text-[15px] no-underline',
                       'font-semibold text-ink shadow-[inset_0_-3px_0_var(--color-amber)]' => $active === $item['id'],
                       'font-medium text-body hover:text-forest' => $active !== $item['id'],
                   ])>{{ $item['label'] }}</a>
            @endforeach
            @guest
                <a href="{{ route('register') }}" class="ml-2.5 flex min-h-11 items-center rounded-xs bg-amber px-5 text-[15px] font-bold text-ink no-underline hover:bg-[#d0952c] hover:text-ink">Register</a>
            @endguest
        </nav>
    </div>
</header>
