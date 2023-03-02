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
            'tempat_absen_masuk',
            'tempat_absen_pulang',
            ];
    }

    public function collection()
    {
        return Absensi::with(['user'])
            ->whereBetween('created_at', [$this->start_time, $this->end_time])
            ->get();
    }

    public function map($kehadiran): array
    {
        if ($kehadiran->is_valid_masuk == 1) {
            $kehadiran->is_valid_masuk = 'Di Sekolah';
        } else {
            $kehadiran->is_valid_masuk = 'Tidak Di Sekolah';
        }

        if ($kehadiran->is_valid_pulang == 1) {
            $kehadiran->is_valid_pulang = 'Di Sekolah';
        } else {
            $kehadiran->is_valid_pulang = 'Tidak Di Sekolah';
        }

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
            $kehadiran->is_valid_masuk,
            $kehadiran->is_valid_pulang,
        ];
    }
}
