<?php

namespace App\Jobs;

use App\Events\NotifEvent;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class NotifEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $events = Event::with('peserta')
            ->whereDate('waktu_mulai', Carbon::now())
            ->get();

        foreach ($events as $event) {
            foreach ($event->peserta as $peserta) {
                event(new NotifEvent($peserta->id, $event->id));
            }
        }
    }
}
