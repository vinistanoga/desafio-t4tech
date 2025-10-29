<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $positions = ['G', 'F', 'C', 'G-F', 'F-C'];
        $currentYear = date('Y');

        return [
            'external_id' => fake()->unique()->numberBetween(1, 10000),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'position' => fake()->randomElement($positions),
            'height' => fake()->randomElement(['6-0', '6-2', '6-5', '6-7', '6-9', '7-0']),
            'weight' => fake()->numberBetween(180, 280),
            'jersey_number' => (string) fake()->numberBetween(0, 99),
            'college' => fake()->optional()->company(),
            'country' => fake()->country(),
            'draft_year' => fake()->optional()->numberBetween(2000, $currentYear),
            'draft_round' => fake()->optional()->numberBetween(1, 2),
            'draft_number' => fake()->optional()->numberBetween(1, 60),
            'team_id' => Team::factory(),
        ];
    }

    public function withoutTeam(): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => null,
        ]);
    }
}
