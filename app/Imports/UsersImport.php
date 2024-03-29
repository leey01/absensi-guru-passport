<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class UsersImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['niy' => $row['niy']], // Kolom unik untuk pengecekan
                [
                    'nama'     => $row['nama'],
                    'email'    => $row['email'],
                    'password' => Hash::make($row['password']),
                    'alamat'   => $row['alamat'],
                    'no_hp'    => $row['no_hp'],
                ]
            );
        }
    }

    public function headingRow(): int
    {
        return 2;
    }
}
