<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserRolePermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $default_user_value = [
            'password' => Hash::make('smkrus'),
            // 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];

        // create user
        $it = User::create(array_merge([
            'niy' => '01234567',
            'nama' => 'IT',
            'email' => 'it@gmail.com',
            'alamat' => 'Temanggung',
            'no_hp' => '081234536675',
            'jenis_user' => 'staff'
        ], $default_user_value));

        $staff = User::create(array_merge([
            'niy' => '00021324',
            'nama' => 'Staff',
            'email' => 'staff@gmail.com',
            'alamat' => 'Kudus',
            'no_hp' => '081245673455',
            'jenis_user' => 'staff'
        ], $default_user_value));

        $guru_anim = User::create(array_merge([
            'niy' => '89282132',
            'nama' => 'Guru Animasi',
            'email' => 'anim@gmail.com',
            'alamat' => 'Semarang',
            'no_hp' => '082466527865',
            'jenis_user' => 'pengajar'
        ], $default_user_value));

        $guru_rpl = User::create(array_merge([
            'niy' => '38493812',
            'nama' => 'Guru RPL',
            'email' => 'erpeel@gmail.com',
            'alamat' => 'Semarang',
            'no_hp' => '082466527865',
            'jenis_user' => 'pengajar'
        ], $default_user_value));

        $guru_dkv = User::create(array_merge([
            'niy' => '03947382',
            'nama' => 'Guru DKV',
            'email' => 'dekape@gmail.com',
            'alamat' => 'Semarang',
            'no_hp' => '082345675434',
            'jenis_user' => 'pengajar'
        ], $default_user_value));

        // create role
        $role_it = Role::create(['name' => 'it']);
        $role_staff = Role::create(['name' => 'staff']);
        $role_guru = Role::create(['name' => 'guru']);

        // assigning role to user
        $it->assignRole('it');
        $staff->assignRole('staff');
        $guru_anim->assignRole('guru');
        $guru_rpl->assignRole('guru');
        $guru_dkv->assignRole('guru');
    }
}
