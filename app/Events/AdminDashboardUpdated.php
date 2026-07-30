<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Gumamit tayo ng ShouldBroadcastNow para instant ang update sa UI
class AdminDashboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        // Walang payload na kailangan, trigger lang ito para mag-fetch ulit ang React ng bagong counts
    }

    public function broadcastOn(): array
    {
        // Pakinggan ito sa private channel na 'admin-dashboard' (THE FIX)
        return [
            new PrivateChannel('admin-dashboard'),
        ];
    }
}
