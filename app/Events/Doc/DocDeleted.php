<?php

namespace App\Events\Doc;

use App\Models\Doc;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $docId;

    private int $projectId;

    /**
     * Create a new event instance.
     */
    public function __construct(Doc $doc)
    {
        $this->docId = $doc->id;
        $this->projectId = $doc->project_id;

        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.Project.{$this->projectId}"),
        ];
    }
}
