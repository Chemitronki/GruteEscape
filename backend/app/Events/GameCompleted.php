<?php

namespace App\Events;

use App\Models\GameSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameSession;

    public function __construct(GameSession $gameSession)
    {
        $this->gameSession = $gameSession;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
