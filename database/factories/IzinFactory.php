<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Izin>
 */
class IzinFactory extends Factory
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
            'jenis_izin' => fake()->randomElement(['sakit', 'izin']),
            'mulai_izin' => fake()->date('Y-m-d'),
            'selesai_izin' => fake()->date('Y-m-d'),
            'deskripsi' => fake()->text(),
            'path_file' => 'path_file/'.fake()->word().'.png',
        ];
    }
}
