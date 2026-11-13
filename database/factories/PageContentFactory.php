<?php

namespace Database\Factories;

use App\Enums\SitePage;
use App\Models\PageContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageContent>
 */
class PageContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page' => SitePage::Home,
            'content' => SitePage::Home->defaults(),
        ];
    }

    public function about(): static
    {
        return $this->state(fn (): array => [
            'page' => SitePage::About,
            'content' => SitePage::About->defaults(),
        ]);
    }
}
