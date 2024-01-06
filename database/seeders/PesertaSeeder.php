<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PesertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pesertas')
            ->insert([
                [
                    'event_id' => 1,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 1,
                    'user_id' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 2,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 2,
                    'user_id' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 3,
                    'user_id' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 3,
                    'user_id' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 4,
                    'user_id' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 4,
                    'user_id' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 5,
                    'user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => 5,
                    'user_id' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
    }
}
