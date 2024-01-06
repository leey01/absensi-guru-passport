<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kategori_karyawan_users')
            ->insert([
                [
                    'user_id' => 1,
                    'kategori_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 2,
                    'kategori_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 2,
                    'kategori_id' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 3,
                    'kategori_id' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 3,
                    'kategori_id' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 4,
                    'kategori_id' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => 5,
                    'kategori_id' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
    }
}
