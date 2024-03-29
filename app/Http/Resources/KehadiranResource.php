<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KehadiranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fotoPathMasuk = $this->foto_masuk;
        $fotoPathPulang = $this->foto_pulang;
        $pf_masuk = Storage::disk('public')->url($fotoPathMasuk);
        $pf_pulang = Storage::disk('public')->url($fotoPathPulang);

        return [
          'waktu_masuk'=> $this->waktu_masuk,
          'waktu_pulang'=> $this->waktu_pulang,
          'tanggal_masuk'=> $this->tanggal_masuk,
          'tanggal_pulang'=> $this->tanggal_pulang,
          'foto_masuk'=> $pf_masuk,
          'foto_pulang'=> $pf_pulang,
          'lokasi_masuk'=> $this->lokasi_masuk,
          'lokasi_pulang'=> $this->lokasi_pulang,
        ];
    }
}
