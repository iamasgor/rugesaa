<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Seed the default membership plans. Prices and benefits are editable later in the admin panel,
     * so existing plans are never overwritten — only missing benefits are filled in.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'student', 'name' => 'Student associate', 'description' => 'For current students of the department.',
                'price' => 0, 'duration_months' => 12, 'sort_order' => 1,
                'benefits' => ['Members portal and alumni directory', 'Webinars, field days and career evenings', 'Meet alumni working in your field'],
            ],
            [
                'slug' => 'general', 'name' => 'General member', 'description' => 'For all graduates — BSc, MS, MPhil and PhD.',
                'price' => 5000, 'duration_months' => 12, 'sort_order' => 2,
                'benefits' => ['Full alumni directory', 'Register for reunions and events', 'Your profile, visible to batchmates', 'Member ID in the portal'],
            ],
            [
                'slug' => 'life', 'name' => 'Life member', 'description' => 'One payment, membership for life.',
                'price' => 10000, 'duration_months' => null, 'sort_order' => 3,
                'benefits' => ['Everything in General, for life', 'No yearly renewal', 'The strongest way to back the association'],
            ],
        ];

        foreach ($plans as $attributes) {
            $plan = MembershipPlan::query()->firstOrCreate(['slug' => $attributes['slug']], $attributes);

            if ($plan->benefits === null) {
                $plan->update(['benefits' => $attributes['benefits']]);
            }
        }
    }
}
