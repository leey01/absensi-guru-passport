<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'niy'      => $row['niy'],
            'nama'     => $row['nama'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password']),
            'alamat'   => $row['alamat'],
            'no_hp'    => $row['no_hp'],
        ]);
    }

    public function headingRow(): int
    {
        return 2;
    }
}
