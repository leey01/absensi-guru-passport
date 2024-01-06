<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Izin extends Model
{
    use HasFactory;

    protected $appends = [
        'link_file'
    ];

    protected $fillable = [
        'user_id',
        'jenis_izin',
        'mulai_izin',
        'selesai_izin',
        'deskripsi',
        'file_name',
        'path_file'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->with('ktgkaryawan');
    }

    public function getLinkFileAttribute()
    {
        if ($this->path_file == null) {
            return null;
        }
        return Storage::disk('public')->url($this->path_file);
    }

    public static function izin()
    {
        $startMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        $data = Izin::where('user_id', Auth::user()->id)
            ->whereDate('selesai_izin', '>=', $startMonth)
            ->whereDate('mulai_izin', '<=', $endMonth)
            ->get();

        return $data ?? null;
    }

    public static function parseDate($datas)
    {
        $datas = $datas->toArray();
        $datas = collect($datas);
        $datas = $datas->sortBy('mulai_izin')->values()->all();

        $rekap = [];
        foreach ($datas as $data) {
            $start = Carbon::parse($data['mulai_izin']);
            $end = Carbon::parse($data['selesai_izin']);
            $data['tanggal'] = $start->format('Y-m-d');

            for ($date = $start; $date->lte($end); $date->addDay()) {
                $rekap[] = array_merge($data, ['tanggal' => $date->format('Y-m-d')]);
            }
        }

        return $rekap;
    }
}
