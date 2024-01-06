<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
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
            'kategori_event' => fake()->randomElement(['event' ,'libur']),
            'lokasi' => fake()->city(),
            'waktu_mulai' => fake()->time(),
            'waktu_selesai' => fake()->time(),
            'deskripsi' => fake()->text(),
        ];
    }
}
