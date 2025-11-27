<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ExcelProgressEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $percent;
    public $message;
    public $userId;

    public function __construct($userId, $percent, $message = '')
    {
        $this->userId  = $userId;
        $this->percent = $percent;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel("excel-progress.{$this->userId}");
    }

    public function broadcastAs()
    {
        return 'progress';
    }
}
