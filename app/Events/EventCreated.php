<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Event $event) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('house.'.$this->event->house_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'event.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->event->id,
            'title' => $this->event->title,
            'description' => $this->event->description,
            'start_datetime' => $this->event->start_datetime,
            'end_datetime' => $this->event->end_datetime,
            'is_active' => $this->event->is_active,
        ];
    }
}
