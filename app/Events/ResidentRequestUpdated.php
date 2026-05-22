<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        // Pakinggan ito sa PRIVATE channel ng mismong resident para secure (App.Models.User.{id})
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }
}