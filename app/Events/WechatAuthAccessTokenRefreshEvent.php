<?php

namespace App\Events;

use App\Entities\MiniProgram;
use App\Entities\OfficialAccount;
use App\Jobs\Job;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Entities\WechatConfig as Wechat;

class WechatAuthAccessTokenRefreshEvent extends Job
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var OfficialAccount|MiniProgram
     * */
    public $wechat = null;

    /**
     * Create a new event instance.
     * @param OfficialAccount|MiniProgram
     * @return void
     */
    public function __construct($config)
    {
        //
        $this->wechat = $config;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}