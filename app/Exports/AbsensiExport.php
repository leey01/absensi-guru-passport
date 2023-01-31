<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{

    use Exportable;

    public function __construct($start_time, $end_time)
    {
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    public function headings(): array
    {
        return [
            'id',
            'nama',
            'keterangan',
            'catatan_masuk',
            'catatan_pulang',
            'waktu_masuk',
            'waktu_pulang',
            'tanggal_masuk',
            'tanggal_pulang',
            'foto_masuk',
            'foto_pulang',
            'lokasi_masuk',
            'lokasi_pulang',
            'longitude_masuk',
            'latitude_masuk',
            'longitude_pulang',
            'latitude_pulang',
        ];
    }

    public function collection()
    {
        return Absensi::with('user')
            ->where('keterangan', 'pulang')
            ->whereBetween('created_at', [$this->start_time, $this->end_time])
            ->get();
    }

    public function map($kehadiran): array
    {
        return [
            $kehadiran->id,
            $kehadiran->user->nama,
            $kehadiran->keterangan,
            $kehadiran->catatan_masuk,
            $kehadiran->catatan_pulang,
            $kehadiran->waktu_masuk,
            $kehadiran->waktu_pulang,
            $kehadiran->tanggal_masuk,
            $kehadiran->tanggal_pulang,
            $kehadiran->foto_masuk,
            $kehadiran->foto_pulang,
            $kehadiran->lokasi_masuk,
            $kehadiran->lokasi_pulang,
            $kehadiran->longitude_masuk,
            $kehadiran->latitude_masuk,
            $kehadiran->longitude_pulang,
            $kehadiran->latitude_pulang,
        ];
    }
}
