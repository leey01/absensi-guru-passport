<?php

namespace App\Events;

use App\Models\Izin;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IzinEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $izin_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($izin_id)
    {
        $this->izin_id = $izin_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('izin-channel');
    }

    public function broadcastAs()
    {
        return 'izin-event';
    }

    public function broadcastWith()
    {
        $izin = Izin::with('user')->find($this->izin_id);
        return [
            'izin' => $izin,
        ];
    }
}
