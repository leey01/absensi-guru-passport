<?php

namespace App\Imports;

use App\Models\Event;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EventsImport implements ToModel, WithHeadingRow
{
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $start_date = ($row['waktu_mulai'] - 25569) * 86400;
        $end_date = ($row['waktu_selesai'] - 25569) * 86400;

//        dd(gmdate('Y-m-d H:i:s', $UNIX_DATE));

        return new Event([
            'user_id'  => $this->user_id,
            'judul' => $row['judul'],
            'kategori_event' => $row['kategori_event'],
            'lokasi' => $row['lokasi'],
            'waktu_mulai' => gmdate('Y-m-d H:i:s', $start_date),
            'waktu_selesai' => gmdate('Y-m-d H:i:s', $end_date),
            'deskripsi' => $row['deskripsi'],
        ]);
    }

    public function headingRow(): int
    {
        return 2;
    }
}
