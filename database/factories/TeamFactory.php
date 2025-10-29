<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conferences = ['East', 'West'];
        $divisions = ['Atlantic', 'Central', 'Southeast', 'Northwest', 'Pacific', 'Southwest'];

        $city = fake()->city();
        $name = fake()->unique()->word();

        return [
            'external_id' => fake()->unique()->numberBetween(1, 1000),
            'conference' => fake()->randomElement($conferences),
            'division' => fake()->randomElement($divisions),
            'city' => $city,
            'name' => ucfirst($name),
            'full_name' => "{$city} " . ucfirst($name),
            'abbreviation' => strtoupper(substr($name, 0, 3)),
        ];
    }
}
