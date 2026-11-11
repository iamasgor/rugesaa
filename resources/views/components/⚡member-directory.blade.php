<?php

use App\Enums\Sector;
use App\Enums\Specialization;
use App\Models\MemberProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $decade = '';

    #[Url(except: '')]
    public string $specialization = '';

    #[Url(except: '')]
    public string $sector = '';

    #[Url(except: false)]
    public bool $mentorsOnly = false;

    public function boot(): void
    {
        Gate::authorize('view-directory');
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'decade', 'specialization', 'sector', 'mentorsOnly');
        $this->resetPage();
    }

    /**
     * Decades from the department's first batch (1955) to now, e.g. ['2020' => '2020s', ...].
     *
     * @return array<string, string>
     */
    #[Computed]
    public function decades(): array
    {
        return collect(range((int) (now()->year / 10) * 10, 1950, -10))
            ->mapWithKeys(fn (int $start): array => [(string) $start => $start.'s'])
            ->all();
    }

    /**
     * @return LengthAwarePaginator<int, MemberProfile>
     */
    #[Computed]
    public function profiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return MemberProfile::query()
            ->listedInDirectory()
            ->with('user:id,name')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query->whereHas('user', fn (Builder $user) => $user->where('name', 'like', $like))
                        ->orWhere('position', 'like', $like)
                        ->orWhere('organization', 'like', $like)
                        ->orWhere('location', 'like', $like)
                        ->orWhere('batch', 'like', $like);
                });
            })
            ->when(preg_match('/^\d{4}$/', $this->decade) === 1, fn (Builder $query) => $query->where('batch', 'like', substr($this->decade, 0, 3).'%'))
            ->when(Specialization::tryFrom($this->specialization), fn (Builder $query, Specialization $specialization) => $query->where('specialization', $specialization))
            ->when(Sector::tryFrom($this->sector), fn (Builder $query, Sector $sector) => $query->where('sector', $sector))
            ->when($this->mentorsOnly, fn (Builder $query) => $query->where('mentor_opt_in', true))
            ->orderByDesc('batch')
            ->orderBy('id')
            ->paginate(12);
    }
};
?>

<section id="directory" aria-labelledby="dir-title" class="flex flex-col gap-6">
    <div class="flex flex-col gap-2">
        <h2 id="dir-title" class="text-[clamp(30px,3.4vw,42px)] leading-[1.05] font-extrabold tracking-tight">Alumni directory</h2>
        <p class="text-base text-muted">Visible to verified members only. You control what appears on your own profile.</p>
    </div>

    <div class="flex flex-col gap-3.5 rounded-sm border border-line bg-card p-5">
        <div class="flex flex-col gap-1.5">
            <label for="directory-search" class="text-sm font-semibold">Search by name, organization, city or batch</label>
            <div class="relative flex">
                <svg class="absolute top-3.5 left-3.5" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4B5B54" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
                <input id="directory-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Try “CEGIS” or “Toronto”" class="min-h-12 flex-1 rounded-xs border border-line-strong bg-white pr-3.5 pl-11 text-base">
            </div>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <label for="directory-decade" class="text-[13px] font-semibold text-body">Batch</label>
                <select id="directory-decade" wire:model.live="decade" class="min-h-11 rounded-xs border border-line-strong bg-white px-2.5 text-[15px]">
                    <option value="">Any batch</option>
                    @foreach ($this->decades as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="directory-specialization" class="text-[13px] font-semibold text-body">Specialization</label>
                <select id="directory-specialization" wire:model.live="specialization" class="min-h-11 rounded-xs border border-line-strong bg-white px-2.5 text-[15px]">
                    <option value="">Any</option>
                    @foreach (Specialization::cases() as $case)
                        <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="directory-sector" class="text-[13px] font-semibold text-body">Profession</label>
                <select id="directory-sector" wire:model.live="sector" class="min-h-11 rounded-xs border border-line-strong bg-white px-2.5 text-[15px]">
                    <option value="">Any sector</option>
                    @foreach (Sector::cases() as $case)
                        <option value="{{ $case->value }}">{{ $case->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-[15px]">
                <input type="checkbox" wire:model.live="mentorsOnly" class="size-5 accent-forest">
                Open to mentoring only
            </label>
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-[15px] text-body" aria-live="polite"><strong class="text-ink">{{ number_format($this->profiles->total()) }}</strong> {{ $this->profiles->total() === 1 ? 'alumnus matches' : 'alumni match' }}</span>
                <button type="button" wire:click="clearFilters" class="min-h-11 cursor-pointer px-1 text-[15px] font-semibold text-forest underline">Clear filters</button>
            </div>
        </div>
    </div>

    <div wire:loading.class="opacity-60" class="grid grid-cols-1 gap-4 transition-opacity sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($this->profiles as $profile)
            <article wire:key="profile-{{ $profile->id }}" class="flex flex-col gap-3.5 rounded-sm border border-line bg-card p-5">
                <div class="flex items-center gap-3.5">
                    @if ($profile->photoUrl())
                        <img src="{{ $profile->photoUrl() }}" alt="" class="size-13 flex-none rounded-full object-cover">
                    @else
                        <span aria-hidden="true" class="flex size-13 flex-none items-center justify-center rounded-full bg-forest text-[17px] font-bold text-paper">{{ $profile->user->initials() }}</span>
                    @endif
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <h3 class="text-lg font-bold">{{ $profile->user->name }}</h3>
                        <span class="font-mono text-xs text-muted">Batch {{ $profile->batch }} · {{ $profile->degree->getLabel() }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-1.5 text-sm text-body">
                    @if ($profile->workSummary())
                        <span class="font-semibold text-ink">{{ $profile->workSummary() }}</span>
                    @endif
                    <span>{{ $profile->specialization->getLabel() }}</span>
                    @if ($profile->location)
                        <span class="flex items-center gap-1.5">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"></path><circle cx="12" cy="9.5" r="2.5"></circle></svg>
                            {{ $profile->location }}
                        </span>
                    @endif
                </div>
                @if ($profile->mentor_opt_in)
                    <span class="mt-auto self-start rounded-xs bg-[#F4E3C1] px-2.5 py-1 text-xs font-semibold text-[#5E3D07]">Open to mentoring</span>
                @endif
            </article>
        @endforeach
    </div>

    @if ($this->profiles->isEmpty())
        <p class="rounded-sm bg-paper-dim p-6 text-base text-body">No one matches those filters yet. Try a wider batch range or clear the filters.</p>
    @endif

    {{ $this->profiles->links() }}
</section>
