<?php

namespace App\Enums;

use App\Models\PageContent;
use Illuminate\Support\Facades\Storage;

/**
 * Public pages whose copy the committee edits in the admin panel.
 */
enum SitePage: string
{
    case Home = 'home';
    case About = 'about';

    /**
     * Association and department contact details plus social links, used site-wide.
     */
    case Contact = 'contact';

    /**
     * The saved content laid over the defaults, section by section, so a section
     * added in code later still renders before anyone has saved it.
     *
     * @return array<string, array<string, mixed>>
     */
    public function content(): array
    {
        $saved = PageContent::query()->where('page', $this)->first();

        return $this->mergeWithDefaults($saved->content ?? []);
    }

    /**
     * @param  array<string, mixed>  $saved
     * @return array<string, array<string, mixed>>
     */
    public function mergeWithDefaults(array $saved): array
    {
        $content = $this->defaults();

        foreach ($content as $section => $fields) {
            if (is_array($saved[$section] ?? null)) {
                $content[$section] = array_merge($fields, $saved[$section]);
            }
        }

        return $content;
    }

    /**
     * URL of an uploaded image, or the bundled image when none has been uploaded.
     */
    public static function imageUrl(mixed $path, string $fallback): string
    {
        return is_string($path) && $path !== '' ? Storage::disk('public')->url($path) : asset($fallback);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function defaults(): array
    {
        return match ($this) {
            self::Contact => self::contactDefaults(),
            self::Home => [
                'hero' => [
                    'eyebrow' => 'Since 1955 · 24.3715° N, 88.6364° E',
                    'title' => 'Stay on the map.',
                    'intro' => 'The alumni network of the Department of Geography & Environmental Studies, University of Rajshahi. Find your batchmates, mentor the next cohort and come home for reunions.',
                    'image' => null,
                ],
                'engage' => [
                    'eyebrow' => 'Get involved',
                    'title' => 'Three ways to stay connected',
                    'items' => [
                        ['title' => 'Find your batchmates', 'body' => 'Search former classmates by batch, specialization, profession or location.', 'cta' => 'Search directory'],
                        ['title' => 'Attend an event', 'body' => 'Reunions, webinars and field days on one calendar, with online registration.', 'cta' => 'Find events'],
                        ['title' => 'Become a member', 'body' => 'Register once, get verified by the committee and unlock the members portal.', 'cta' => 'Register'],
                    ],
                ],
                'benefits' => [
                    'eyebrow' => 'Member benefits',
                    'title' => 'Your network works for you',
                    'intro' => 'Register once to unlock the members portal: the alumni directory, your membership record and early reunion booking.',
                    'directory_title' => 'Where did your batchmates end up?',
                    'directory_body' => 'Search by name, batch, specialization, profession or city. The directory is open to verified members.',
                    'steps_title' => 'How membership works',
                    'steps' => [
                        'Register with your batch, student ID and current work.',
                        'Pay the fee online with bKash, Nagad, Rocket or card. Students join free.',
                        'The committee checks your batch against department records and activates your membership.',
                    ],
                ],
                'events' => [
                    'eyebrow' => 'Reunions & events',
                    'title' => 'Come home to the department',
                    'body' => 'Batch reunions, lab tours, webinars and field days. Members register online for every gathering.',
                    'image' => null,
                ],
                'news' => [
                    'eyebrow' => 'From the network',
                    'title' => 'News & stories',
                    'intro' => "What alumni are doing and what's new in the department.",
                ],
                'join' => [
                    'eyebrow' => 'Membership',
                    'title' => 'Your degree came with a network. Claim it.',
                    'body' => 'Students join free as associates. Graduates choose a yearly or a life membership.',
                ],
                'contact' => [
                    'eyebrow' => 'Stay in touch',
                    'title' => 'Questions? Write to us.',
                    'body' => 'Membership, reunions or your directory profile — the committee replies by email.',
                ],
            ],
            self::About => [
                'hero' => [
                    'title' => 'People who read landscapes, keeping in touch.',
                    'intro' => 'The Rajshahi University Geography & Environmental Studies Alumni Association (RUGESAA) connects everyone who has studied in the Department of Geography & Environmental Studies at the University of Rajshahi — and puts that network to work for its students.',
                ],
                'mission' => [
                    'eyebrow' => 'Our mission',
                    'title' => 'Keep the cohort together long after the final field report.',
                    'body' => 'Since 1955 the department has trained geographers who now work in research, government, planning and development, in Bangladesh and abroad. The association keeps them connected to each other and to the department — and puts that network to work for the roughly 400 students studying here today.',
                    'pillars' => [
                        ['heading' => 'Connect', 'body' => 'A searchable directory of every batch, kept current by members.'],
                        ['heading' => 'Mentor', 'body' => 'Senior alumni guide students into careers in research, planning and policy.'],
                        ['heading' => 'Gather', 'body' => 'Reunions, webinars and field days that bring batches back together.'],
                        ['heading' => 'Give back', 'body' => 'Scholarships, field research grants and support for department labs.'],
                    ],
                ],
                'history' => [
                    'eyebrow' => 'History',
                    'title' => 'A department, a discipline and the people it shaped',
                    'body' => 'The department began work in 1955 as the Department of Geography, with a few teachers and students and a two-year postgraduate course. It is now one of the oldest and largest departments at the University of Rajshahi, and it has grown almost continuously since — faster still after the liberation of Bangladesh in 1971.',
                    'milestones' => [
                        ['year' => '1955', 'heading' => 'Department of Geography opens', 'body' => 'The first batch is admitted to a two-year postgraduate course.'],
                        ['year' => '1962', 'heading' => 'Honours course begins', 'body' => 'A three-year honours programme is introduced.'],
                        ['year' => '1971', 'heading' => 'Growth after liberation', 'body' => "The department grows faster after Bangladesh's independence."],
                        ['year' => '1991–92', 'heading' => 'Integrated honours', 'body' => 'The integrated honours course starts this session.'],
                        ['year' => '1997–98', 'heading' => 'Four-year honours', 'body' => 'A four-year honours course is introduced.'],
                        ['year' => 'Today', 'heading' => 'Faculty of Geoscience', 'body' => 'Geography & Environmental Studies, with 24 teachers and around 400 students.'],
                    ],
                    'image' => null,
                    'image_alt' => 'A modern campus building with people walking on the lawn',
                    'image_caption' => 'Today · The department building',
                ],
                'department' => [
                    'eyebrow' => 'The department today',
                    'title' => 'Physical and human geography, under one roof',
                    'body' => 'Undergraduate courses balance the physical and human branches of geography; postgraduate courses build specialized knowledge. The department sits in the Faculty of Geoscience and publishes an annual journal, The Journal of Geo-Environment (ISSN 1682-1998).',
                    'stats' => [
                        ['figure' => '1955', 'label' => 'Founded as the Department of Geography'],
                        ['figure' => '24', 'label' => 'Teachers, 13 of them professors'],
                        ['figure' => '~400', 'label' => 'Students across all programmes'],
                        ['figure' => '6', 'label' => 'Laboratories and research units'],
                    ],
                    'laboratories' => [
                        ['name' => 'GIS & Remote Sensing Laboratory', 'description' => 'High-speed networked PCs, wide-screen monitors, a large-format digitizer and colour plotter, linked to the campus fibre-optic LAN.'],
                        ['name' => 'Survey Laboratory', 'description' => 'Plane table and prismatic compass kits, theodolite, dumpy level, GPS, barometric altimeters and an echo-depth finder.'],
                        ['name' => 'Physical Geography Laboratory', 'description' => 'Landform models, rock and mineral testing, particle measurement, and arsenic, pollution, pH and water testing.'],
                        ['name' => 'Climatology Laboratory', 'description' => 'An automatic weather station that records 13 types of weather data every hour.'],
                        ['name' => 'Cartography Laboratory', 'description' => 'Large-scale topographic maps of all Bangladesh from two periods, weather maps, 1978–79 satellite image hardcopies, maps and atlases.'],
                        ['name' => 'Geo-informatics Research Unit', 'description' => "Multi-date satellite data for the whole country, major GIS and remote sensing software, and vector data for Bangladesh's administrative units."],
                    ],
                    'partners_abroad' => ['University of Education, Freiburg', 'University of Cologne', 'University of Münster', 'IDRISI Lab, Clark University', 'Universiti Teknologi Malaysia'],
                    'partners_local' => ['SPARRSO', 'Bangladesh Meteorological Department', 'Center for Environment and GIS', 'Soil Resource Development Institute', 'Bangladesh Agricultural Research Council'],
                ],
                'documents' => [
                    'eyebrow' => 'Governance',
                    'title' => 'Constitution & reports',
                    'body' => 'How the association is run, how committee members are elected and how funds are spent — published for every member.',
                ],
            ],
        };
    }

    /**
     * Contact details as configured in `config/rugesaa.php` (and `.env`) before an admin saves any.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function contactDefaults(): array
    {
        /** @var array<string, string|null> $social */
        $social = config('rugesaa.social', []);

        return [
            'association' => [
                'email' => config('rugesaa.email'),
                'phone' => config('rugesaa.phone'),
                'office_hours' => config('rugesaa.office_hours'),
            ],
            'social' => [
                'links' => collect($social)
                    ->map(fn (?string $url, string $label): array => ['label' => $label, 'url' => $url])
                    ->values()
                    ->all(),
            ],
            'department' => config('rugesaa.department'),
        ];
    }
}
