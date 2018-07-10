<?php

namespace App\Listeners;

use App\Entities\Ticket;
use App\Events\SyncTicketCardInfoEvent;

class SyncTicketCardInfoEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SyncTicketCardInfoEvent  $event
     * @return void
     * @throws
     */
    public function handle(SyncTicketCardInfoEvent $event)
    {
        //
        $ticket = $event->ticket;
        if(!$ticket->wechatAppId) {
            $ticket = Ticket::with('app')->find($ticket->id);
            $ticket->wechatAppId = $ticket->app->wechatAppId;
            $ticket->save();
        }

        if($ticket->sync === Ticket::SYNC_ING) {
            sleep(10);
        }

        if($ticket->cardId === null) {
            $result = $event->wechat->card->create($ticket->cardType, $ticket->cardInfo);
        } else {
            $result = $event->wechat->card->update($ticket->cardId, $ticket->cardType, $event->ticketInfo);
        }

        if($result['errcode'] === 0) {
            $app = $ticket->app()->first();
            $ticket->cardId = $result['card_id'];
            $ticket->wechatAppId = $app->wechatAppId;
            $ticket->sync = Ticket::SYNC_SUCCESS;
            $ticket->save();
        } else {
            $ticket->sync = Ticket::SYNC_FAILED;
            $ticket->save();
            throw new \Exception($result['errmsg']);
        }

    }
}
