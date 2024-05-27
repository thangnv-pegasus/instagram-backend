<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnLike implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $likesCount;
    public $name;

    /**
     * Create a new event instance.
     */
    public function __construct($id, $name, $likesCount)
    {
        $this->id = $id;
        $this->likesCount = $likesCount;
        $this->name = $name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->name === 'post') {
            return [
                new Channel('like-post'),
            ];
        } else if ($this->name === 'parent') {
            return [
                new Channel('like-parent-comment'),
            ];
        }
        return [
            new Channel('like-child-comment'),
        ];
    }
    public function broadcastAs()
    {
        return 'like-event';
    }
}
