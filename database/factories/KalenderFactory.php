<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kalender>
 */
class KalenderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => mt_rand(1,5),
            'judul' => fake()->words(3, true),
            'deskripsi' => fake()->text(),
            'tanggal' => fake()->date('Y-m-d'),
            'untuk' => fake()->randomElement(['all' ,'guru', 'staff']),
            'is_libur' => fake()->randomElement([1, 0]),
        ];
    }
}
