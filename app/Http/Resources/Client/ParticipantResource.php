<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pf_foto = Storage::disk('public')->url($this->pf_foto);

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'pf_foto' => $pf_foto,
        ];
    }
}
