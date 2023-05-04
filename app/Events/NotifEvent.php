<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user_id;
    public $event;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $event)
    {
        $this->user_id = $user;
        $this->event = $event;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        Log::debug("Event: {$this->event}, User: {$this->user_id}");
        return [
            new PrivateChannel("notif.event.{$this->user_id}")
        ];
    }

    public function broadcastAs()
    {
        return 'notif-event';
    }

    public function broadcastWith()
    {
        $event = Event::find($this->event);
        return [
            'event' => $event,
            'user_id' => $this->user_id,
        ];
    }

}
