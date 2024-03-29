<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug,
            'founding_date' => fake()->dateTimeBetween('--30 years', 'now'),
            'rating' => fake()->randomFloat(2,0, 10),
        ];
    }
}
