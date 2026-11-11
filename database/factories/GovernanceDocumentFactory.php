<?php

namespace Database\Factories;

use App\Models\GovernanceDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GovernanceDocument>
 */
class GovernanceDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => 'Annual report '.fake()->year(),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'sort_order' => 0,
        ];
    }
}
