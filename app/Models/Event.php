<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at', 'pivot', 'user_id'];

    public function peserta()
    {
        return $this->belongsToMany(User::class, 'pesertas', 'event_id', 'user_id')->with('ktgkaryawan');
    }

}
