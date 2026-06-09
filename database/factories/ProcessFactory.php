<?php

namespace Database\Factories;

use App\Models\Pc;
use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pc_id' => Pc::factory(),
            'process_start' => now(),
            'process_name' => $this->faker->word().'.exe',
            'window_name' => $this->faker->sentence(),
            'duration' => $this->faker->numberBetween(10, 3600),
        ];
    }
}
