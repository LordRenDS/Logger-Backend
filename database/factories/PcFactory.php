<?php

namespace Database\Factories;

use App\Models\Pc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pc>
 */
class PcFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'unique_id' => fake()->uuid(),
            'name' => fake()->word().' PC',
            'last_seen_at' => now(),
        ];
    }
}
