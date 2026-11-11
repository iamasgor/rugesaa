@php
    $sectionTitle = 'text-[clamp(34px,4vw,52px)] leading-[1.02] font-extrabold tracking-tight';
    $department = config('rugesaa.department');
    $mapUrl = 'https://www.google.com/maps?q='.$department['latitude'].','.$department['longitude'];

    $milestones = [
        ['1955', 'Department of Geography opens', 'The first batch is admitted to a two-year postgraduate course.'],
        ['1962', 'Honours course begins', 'A three-year honours programme is introduced.'],
        ['1971', 'Growth after liberation', "The department grows faster after Bangladesh's independence."],
        ['1991–92', 'Integrated honours', 'The integrated honours course starts this session.'],
        ['1997–98', 'Four-year honours', 'A four-year honours course is introduced.'],
        ['Today', 'Faculty of Geoscience', 'Geography & Environmental Studies, with 24 teachers and around 400 students.'],
    ];

    $laboratories = [
        ['GIS & Remote Sensing Laboratory', 'High-speed networked PCs, wide-screen monitors, a large-format digitizer and colour plotter, linked to the campus fibre-optic LAN.'],
        ['Survey Laboratory', 'Plane table and prismatic compass kits, theodolite, dumpy level, GPS, barometric altimeters and an echo-depth finder.'],
        ['Physical Geography Laboratory', 'Landform models, rock and mineral testing, particle measurement, and arsenic, pollution, pH and water testing.'],
        ['Climatology Laboratory', 'An automatic weather station that records 13 types of weather data every hour.'],
        ['Cartography Laboratory', 'Large-scale topographic maps of all Bangladesh from two periods, weather maps, 1978–79 satellite image hardcopies, maps and atlases.'],
        ['Geo-informatics Research Unit', "Multi-date satellite data for the whole country, major GIS and remote sensing software, and vector data for Bangladesh's administrative units."],
    ];
@endphp

<x-layouts.site title="About" active="about">
    <x-site.page-hero
        title="People who read landscapes, keeping in touch."
        breadcrumb="About"
        intro="The Rajshahi University Geography & Environmental Studies Alumni Association (RUGESAA) connects everyone who has studied in the Department of Geography & Environmental Studies at the University of Rajshahi — and puts that network to work for its students.">
        <nav aria-label="On this page" class="flex flex-wrap gap-2.5 pt-3">
            @foreach (['mission' => 'Mission', 'history' => 'History', 'department' => 'The department today', 'committee' => 'Executive committee', 'documents' => 'Constitution'] as $anchor => $label)
                @continue($anchor === 'committee' && $featuredCommittee->isEmpty() && $otherCommittee->isEmpty())
                @continue($anchor === 'documents' && $documents->isEmpty())
                <a href="#{{ $anchor }}" class="flex min-h-10 items-center rounded-full border border-[#6B887C] px-4 text-[15px] text-paper no-underline hover:border-amber hover:text-amber">{{ $label }}</a>
            @endforeach
        </nav>
    </x-site.page-hero>

    <section id="mission" aria-labelledby="mission-title" class="mx-auto grid max-w-7xl scroll-mt-6 grid-cols-1 gap-x-16 gap-y-12 px-4 pt-26 sm:px-8 lg:grid-cols-2">
        <div class="flex flex-col gap-4">
            <x-site.eyebrow>Our mission</x-site.eyebrow>
            <h2 id="mission-title" class="text-[clamp(32px,3.6vw,48px)] leading-[1.05] font-extrabold tracking-tight">Keep the cohort together long after the final field report.</h2>
            <p class="text-[17px] leading-relaxed text-body">Since 1955 the department has trained geographers who now work in research, government, planning and development, in Bangladesh and abroad. The association keeps them connected to each other and to the department — and puts that network to work for the roughly 400 students studying here today.</p>
        </div>
        <div class="grid grid-cols-1 gap-px self-start overflow-hidden rounded-sm border border-line bg-line sm:grid-cols-2">
            @foreach ([['Connect', 'A searchable directory of every batch, kept current by members.'], ['Mentor', 'Senior alumni guide students into careers in research, planning and policy.'], ['Gather', 'Reunions, webinars and field days that bring batches back together.'], ['Give back', 'Scholarships, field research grants and support for department labs.']] as [$heading, $body])
                <div class="flex flex-col gap-2.5 bg-card p-6">
                    <span class="font-mono text-xs text-amber-deep">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <h3 class="text-[19px] font-bold">{{ $heading }}</h3>
                    <p class="text-[15px] leading-normal text-muted">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="history" aria-labelledby="history-title" class="mt-28 scroll-mt-6 bg-paper-dim">
        <div class="mx-auto flex max-w-7xl flex-col gap-14 px-4 py-24 sm:px-8">
            <div class="grid grid-cols-1 items-end gap-x-16 gap-y-6 lg:grid-cols-2">
                <div class="flex flex-col gap-3.5">
                    <x-site.eyebrow>History</x-site.eyebrow>
                    <h2 id="history-title" class="text-[clamp(34px,4vw,56px)] leading-[1.02] font-extrabold tracking-tight">A department, a discipline and the people it shaped</h2>
                </div>
                <p class="text-[17px] leading-relaxed text-body">The department began work in 1955 as the Department of Geography, with a few teachers and students and a two-year postgraduate course. It is now one of the oldest and largest departments at the University of Rajshahi, and it has grown almost continuously since — faster still after the liberation of Bangladesh in 1971.</p>
            </div>
            <ol class="grid grid-cols-2 gap-y-7 border-t-2 border-forest sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($milestones as [$year, $heading, $body])
                    <li class="relative flex flex-col gap-2.5 pt-7 pr-5">
                        <span aria-hidden="true" @class(['absolute -top-[9px] left-0 size-4 rounded-full border-2 border-forest', 'bg-amber' => $loop->first || $loop->last, 'bg-paper' => ! ($loop->first || $loop->last)])></span>
                        <span class="text-[28px] font-extrabold text-forest">{{ $year }}</span>
                        <h3 class="text-[17px] font-bold">{{ $heading }}</h3>
                        <p class="text-[15px] leading-normal text-muted">{{ $body }}</p>
                    </li>
                @endforeach
            </ol>
            <figure class="flex flex-col gap-2.5 lg:max-w-2xl">
                <img src="{{ asset('images/department.jpg') }}" alt="A modern campus building with people walking on the lawn" class="block aspect-[4/3] w-full rounded-sm bg-[#C3CEC4] object-cover">
                <figcaption class="font-mono text-xs tracking-wide text-muted">Today · The department building</figcaption>
            </figure>
            @if (config('rugesaa.email'))
                <a href="mailto:{{ config('rugesaa.email') }}" class="self-start text-base font-semibold">Have old photos or stories? Share them with the archive →</a>
            @endif
        </div>
    </section>

    <section id="department" aria-labelledby="dept-title" class="mx-auto flex max-w-7xl scroll-mt-6 flex-col gap-12 px-4 pt-26 sm:px-8">
        <div class="grid grid-cols-1 items-end gap-x-16 gap-y-6 lg:grid-cols-2">
            <div class="flex flex-col gap-3.5">
                <x-site.eyebrow>The department today</x-site.eyebrow>
                <h2 id="dept-title" class="{{ $sectionTitle }}">Physical and human geography, under one roof</h2>
            </div>
            <p class="text-[17px] leading-relaxed text-body">Undergraduate courses balance the physical and human branches of geography; postgraduate courses build specialized knowledge. The department sits in the Faculty of Geoscience and publishes an annual journal, <em>The Journal of Geo-Environment</em> (ISSN 1682-1998).</p>
        </div>
        <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-sm border border-line bg-line sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['1955', 'Founded as the Department of Geography'], ['24', 'Teachers, 13 of them professors'], ['~400', 'Students across all programmes'], ['6', 'Laboratories and research units']] as [$figure, $label])
                <div class="flex flex-col-reverse gap-1.5 bg-card p-6">
                    <dt class="text-[15px] text-muted">{{ $label }}</dt>
                    <dd class="text-[44px] leading-none font-extrabold text-forest">{{ $figure }}</dd>
                </div>
            @endforeach
        </dl>
        <div class="flex flex-col gap-3.5">
            <h3 class="text-xl font-bold">Research areas</h3>
            <ul class="flex flex-wrap gap-2">
                @foreach (App\Enums\Specialization::cases() as $area)
                    @continue($area === App\Enums\Specialization::Other)
                    <li class="rounded-full bg-paper-dim px-3.5 py-2 text-[15px]">{{ $area->getLabel() }}</li>
                @endforeach
            </ul>
        </div>
        <div class="flex flex-col gap-3.5">
            <h3 class="text-xl font-bold">Laboratories</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($laboratories as [$name, $description])
                    <div class="flex flex-col gap-2 rounded-sm border border-line bg-card p-5.5">
                        <span class="text-[17px] font-bold">{{ $name }}</span>
                        <span class="text-[15px] leading-normal text-muted">{{ $description }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (['Partners abroad' => ['University of Education, Freiburg', 'University of Cologne', 'University of Münster', 'IDRISI Lab, Clark University', 'Universiti Teknologi Malaysia'], 'Partners in Bangladesh' => ['SPARRSO', 'Bangladesh Meteorological Department', 'Center for Environment and GIS', 'Soil Resource Development Institute', 'Bangladesh Agricultural Research Council']] as $heading => $partners)
                <div class="flex flex-col gap-3 rounded-sm bg-paper-dim p-6">
                    <h3 class="text-lg font-bold">{{ $heading }}</h3>
                    <ul class="flex list-disc flex-col gap-1.5 pl-4.5 text-[15px] leading-normal text-body">
                        @foreach ($partners as $partner)
                            <li>{{ $partner }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <div class="flex flex-col gap-2.5 rounded-sm bg-ink p-6 text-paper">
                <h3 class="text-lg font-bold">Department office</h3>
                <span class="text-[15px] leading-normal"><strong>{{ $department['chairman'] }}</strong><br>Professor &amp; Chairman</span>
                <span class="text-[15px] leading-normal text-mist">{{ $department['address'] }}</span>
                <a href="mailto:{{ $department['email'] }}" class="text-[15px] text-paper">{{ $department['email'] }}</a>
                <span class="text-[15px] text-mist">Phone &amp; fax: {{ $department['phone'] }}</span>
                <a href="{{ $mapUrl }}" rel="noopener" class="font-mono text-[13px] text-amber">24.3715° N, 88.6364° E · Open map</a>
            </div>
        </div>
    </section>

    @if ($featuredCommittee->isNotEmpty() || $otherCommittee->isNotEmpty())
        <section id="committee" aria-labelledby="committee-title" class="mx-auto flex max-w-7xl scroll-mt-6 flex-col gap-10 px-4 pt-26 sm:px-8">
            <div class="flex flex-col gap-3">
                <x-site.eyebrow>Leadership</x-site.eyebrow>
                <h2 id="committee-title" class="{{ $sectionTitle }}">Executive committee</h2>
            </div>
            @if ($featuredCommittee->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featuredCommittee as $member)
                        <div class="flex flex-col gap-3.5">
                            @if ($member->photoUrl())
                                <img src="{{ $member->photoUrl() }}" alt="Portrait of {{ $member->name }}" class="aspect-square w-full rounded-sm bg-[#CBD6CC] object-cover">
                            @else
                                <div aria-hidden="true" class="flex aspect-square w-full items-center justify-center rounded-sm bg-[#CBD6CC] text-5xl font-extrabold text-forest/40">{{ Str::initials($member->name) }}</div>
                            @endif
                            <div class="flex flex-col gap-1">
                                <span class="text-[19px] font-bold">{{ $member->name }}</span>
                                <span class="text-[15px] font-semibold text-amber-deep">{{ $member->position }}</span>
                                <span class="text-sm text-muted">{{ collect([$member->batch ? 'Batch '.$member->batch : null, $member->current_role])->filter()->join(' · ') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if ($otherCommittee->isNotEmpty())
                <div class="flex flex-wrap gap-x-7 gap-y-2.5 border-y border-line py-5 text-[15px] text-body">
                    <span class="font-semibold text-ink">{{ $featuredCommittee->isNotEmpty() ? 'Also on the committee:' : 'Committee:' }}</span>
                    @foreach ($otherCommittee as $member)
                        <span>{{ $member->position }} · {{ $member->name }}</span>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($documents->isNotEmpty())
        <section id="documents" aria-labelledby="docs-title" class="mx-auto max-w-7xl scroll-mt-6 px-4 pt-26 sm:px-8">
            <div class="grid grid-cols-1 gap-10 rounded-sm bg-ink p-8 text-paper sm:p-12 lg:grid-cols-2">
                <div class="flex flex-col gap-3.5">
                    <x-site.eyebrow tone="text-amber">Governance</x-site.eyebrow>
                    <h2 id="docs-title" class="text-[clamp(30px,3.4vw,44px)] leading-[1.05] font-extrabold tracking-tight">Constitution &amp; reports</h2>
                    <p class="text-[17px] leading-relaxed text-mist">How the association is run, how committee members are elected and how funds are spent — published for every member.</p>
                </div>
                <ul class="flex flex-col">
                    @foreach ($documents as $document)
                        <li @class(['border-t border-forest-line', 'border-b' => $loop->last])>
                            <a href="{{ $document->url() }}" class="flex min-h-15 items-center justify-between gap-4 text-[17px] font-semibold text-paper no-underline hover:text-amber">
                                {{ $document->title }}<span class="font-mono text-xs text-mist-dim">{{ $document->fileExtension() }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <div class="pb-28"></div>
</x-layouts.site>
