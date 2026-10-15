<?php

namespace App\Events;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidentRequestUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $message;

    public function __construct(mixed $userOrId, ?string $message = null)
    {
        if ($userOrId instanceof ServiceRequest) {
            $this->userId = $userOrId->user_id;
            $this->message = $message ?? ($userOrId->rejection_reason ?? 'Request Updated');
        } elseif ($userOrId instanceof User) {
            $this->userId = $userOrId->id;
            $this->message = $message;
        } else {
            $this->userId = $userOrId;
            $this->message = $message;
        }
    }

    public function broadcastOn(): array
    {
        // THE FIX: Tumugma sa pinasimpleng channel name sa routes/channels.php
        return [
            new PrivateChannel('resident.'.$this->userId),
        ];
    }
}
