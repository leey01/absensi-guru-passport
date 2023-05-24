<?php

namespace App\Events;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class KehadiranPulangEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $kehadiran_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($kehadiran_id)
    {
        $this->kehadiran_id = $kehadiran_id;
    }

    public function broadcastAs()
    {
        return 'kehadiran-keluar-event';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('kehadiran-keluar-channel');
    }

    public function broadcastWith()
    {
        $kehadiran = Absensi::with('user')->find($this->kehadiran_id);
        return [
            'kehadiran' => $kehadiran,
        ];
    }
}
