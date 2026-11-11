<?php

namespace Database\Factories;

use App\Enums\Degree;
use App\Enums\Sector;
use App\Enums\Specialization;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberProfile>
 */
class MemberProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(1990, 2022);

        return [
            'user_id' => User::factory(),
            'phone' => '+8801'.fake()->numerify('#########'),
            'degree' => fake()->randomElement(Degree::cases()),
            'batch' => $startYear.'-'.substr((string) ($startYear + 1), -2),
            'student_id' => fake()->numerify('##########'),
            'hall' => fake()->optional()->word(),
            'specialization' => fake()->randomElement(Specialization::cases()),
            'position' => fake()->jobTitle(),
            'organization' => fake()->company(),
            'sector' => fake()->randomElement(Sector::cases()),
            'location' => fake()->city().', Bangladesh',
            'mentor_opt_in' => fake()->boolean(),
            'show_in_directory' => true,
            'newsletter_opt_in' => true,
        ];
    }
}
