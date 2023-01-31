<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Absensi>
 */
class AbsensiFactory extends Factory
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
            'keterangan' => 'masuk',
            'catatan_masuk' => fake()->sentence(),
            'waktu_masuk' => fake()->time(),
            'tanggal_masuk' => fake()->date('Y-m-d'),
            'foto_masuk' => 'foto_masuk/'.fake()->word().'.png',
            'lokasi_masuk' => fake()->city(),
            'longitude_masuk' => fake()->randomNumber(7, true),
            'latitude_masuk' => fake()->randomNumber(7, true),
        ];
    }
}
