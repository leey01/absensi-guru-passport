<?php

namespace App\Jobs;

use App\Events\NotifEvent;
use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
        $today = \Carbon\Carbon::now()->format('Y-m-d');
        $events = Event::with('peserta')
            ->whereRaw('DATE(waktu_mulai) <= ?', [$today])
            ->whereRaw('DATE(waktu_selesai) >= ?', [$today])
            ->get();

        $SERVER_API_KEY = env('FCM_SERVER_KEY', 'IssUb7pRvHfgY1q7hNfB0M3ZtSRTfmSc0'); // ini dari file .env (Server key
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
        $ch = curl_init();

        foreach ($events as $event) {
            $usersToken = [];

            foreach ($event->peserta as $peserta) {
                $deviceToken = User::whereNotNull('device_token')
                    ->where('id', $peserta->id)
                    ->pluck('device_token')
                    ->all();

                $usersToken = array_merge($usersToken, $deviceToken);
            }

            $data = [
                "registration_ids" => $usersToken,
                "notification" => [
                    "title" => "Pemberitahuan Acara",
                    "body" => "Hari ini anda punya acara $event->judul",
                    "content_available" => true,
                    "priority" => "high",
                ],
                "data" => [
                    "icon" => url('/storage/icon/icon-rus.png')
                ]
            ];
            $dataString = json_encode($data);

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);

            // Log response for debugging purposes
            Log::info($response);

            // You can also add error handling here if needed
            if ($response === false) {
                Log::error(curl_error($ch));
            }
        }

        curl_close($ch);
    }
}
