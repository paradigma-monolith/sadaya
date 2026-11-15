<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class RunSimplyAutoSyncJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(public readonly ?int $requestedByUserId = null) {}

    /**
     * Execute the queued Simply synchronization command and notify the requester.
     */
    public function handle(): void
    {
        Artisan::call('simply:auto-sync', ['--force' => true]);

        if ($this->requestedByUserId === null) {
            return;
        }

        $user = User::find($this->requestedByUserId);
        if ($user === null) {
            return;
        }

        $user->notify(new SystemAlertNotification(
            'Sinkronisasi Simply Selesai',
            'Sinkronisasi basis data modul Simply telah selesai dijalankan di latar belakang.',
            'success',
            '/data-migration'
        ));
    }
}
