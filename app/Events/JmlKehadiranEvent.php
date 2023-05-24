<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class JmlKehadiranEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tanggal;

    public int $jmlKaryawan;
    public int $jmlMasuk;
    public int $jmlIzin;
    public int $jmlAbsen;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->jmlKaryawan = $data['jumlah_karyawan'];
        $this->jmlMasuk = $data['jumlah_masuk'];
        $this->jmlIzin = $data['jumlah_izin'];
        $this->jmlAbsen = $data['jumlah_absen'];
        $this->tanggal = $data['tanggal'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new Channel("jml-kehadiran-channel")
        ];
    }

    public function broadcastAs()
    {
        return 'jml-kehadiran-event';
    }

    public function broadcastWith()
    {
        return [
            'jumlah_karyawan' => $this->jmlKaryawan,
            'jumlah_masuk' => $this->jmlMasuk,
            'jumlah_izin' => $this->jmlIzin,
            'jumlah_absen' => $this->jmlAbsen,
            'tanggal' => $this->tanggal,
        ];
    }
}
