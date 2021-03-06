<?php

namespace App\Console\Commands;

use App\Events\OrderPaidNoticeEvent;
use App\Events\Socket\TestEvent;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Console\Command;
use Illuminate\Queue\Connectors\BeanstalkdConnector;
use Illuminate\Support\Facades\Log;
use Workerman\Protocols\Websocket;

class BroadcastEventCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcast:event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
//        broadcast(new OrderPaidNoticeEvent(1));
//        publish('test', 'event1', 'test message');
//        app(BroadcastManager::class)->event(new OrderPaidNoticeEvent(1));
//        app('redis')->publish('usr','test:value');
//        $result = app('redis')->publish('usr', 'test message');
//        Log::info('channel result ', [$result, app('redis')->keys('*')]);
        broadcasting(new TestEvent());
        $this->info('channel result  '.json_encode([app('redis')->keys('*')]));
    }
}
