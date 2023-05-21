<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
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
                    'key' => 'longitude',
                    'value' => '106.827153',
                ],
                [
                    'key' => 'latitude',
                    'value' => '-6.175392',
                ],
                [
                    'key' => 'radius',
                    'value' => '100',
                ],
        ]);
    }
}
