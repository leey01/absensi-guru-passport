<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\Jadwal;
use App\Models\Event;
use App\Models\KategoriKaryawan;
use App\Models\KategoriKaryawanUser;
use App\Models\Peserta;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([UserRolePermission::class]);

        Absensi::factory(6)->create();
        Izin::factory(5)->create();
        Jadwal::factory(10)->create();
        Event::factory(5)->create();
        KategoriKaryawan::factory(5)->create();
        $this->call([KategoriKaryawanSeeder::class]);
        $this->call([PesertaSeeder::class]);
        Setting::factory(5)->create();
    }
}
