<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BatasWaktuAbsenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
                [
                    'key' => 'batas_waktu_absen_masuk',
                    'value' => '01:00:00'
                ],
                [
                    'key' => 'batas_waktu_absen_pulang',
                    'value' => '01:00:00'
                ],
        ]);
    }
}
