<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class KaryawanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $pf_profile = Storage::disk('public')->url($this->pf_foto);

        return [
            'id' => $this->id,
            'niy' => $this->id,
            'nama' => $this->nama,
            'email' => $this->email,
            'alamat' => $this->alamat,
            'no_hp' => $this->no_hp,
            'jenis_user' => $this->jenis_user,
            'pf_foto' => $pf_profile,
        ];
    }
}
