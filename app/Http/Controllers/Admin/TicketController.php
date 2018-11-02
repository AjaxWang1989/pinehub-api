<?php

namespace App\Http\Controllers\Admin;

use App\Criteria\Admin\SearchRequestCriteria;
use App\Criteria\Admin\TicketCriteria;
use App\Entities\CustomerTicketCard;
use App\Entities\Ticket;
use App\Events\SyncTicketCardInfoEvent;
use App\Http\Requests\Admin\TicketUpdateRequest;
use App\Http\Requests\Admin\TicketCreateRequest;
use App\Repositories\TicketRepository;
use App\Http\Controllers\Admin\CardsController as Controller;
use App\Transformers\TicketItemTransformer;
use App\Transformers\TicketTransformer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;

class TicketController extends Controller
{
    //
    use ControllerTrait;

    protected $ticketRepository = null;

    public function __construct(TicketRepository $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
        parent::__construct($ticketRepository);
    }


    /**
     *创建现金/折扣券
     * @param TicketCreateRequest $request
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function store(TicketCreateRequest $request)
    {
        $ticket = parent::store($request);

        if ($request->input('sync', false)) {
            $ticket = new Ticket(with($ticket, function (Ticket $card) {
                return $card->toArray();
            }));
            $ticket->exists = true;
            Event::fire(new SyncTicketCardInfoEvent($ticket, [], app('wechat')->officeAccount()));
        }
        return $this->response()->item($ticket, new TicketTransformer());
    }

    /**
     * 获取优惠券列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->ticketRepository->pushCriteria(app(TicketCriteria::class));
        $this->ticketRepository->pushCriteria(app(SearchRequestCriteria::class));
        $this->ticketRepository->withCount([
            'customerTickets as user_get_count' => function (Builder $query) {
                return $query->whereNotNull('customer_id');
            },
            'customerTickets as used_count' => function (Builder $query) {
                return $query->whereNotNull('customer_id')->where('status', CustomerTicketCard::STATUS_USE);
            }
        ]);
        $tickets = parent::index(); // TODO: Change the autogenerated stub

        return $this->response()->paginator($tickets, new TicketItemTransformer());
    }

    /**
     *
     * @param TicketUpdateRequest $request
     * @param $id
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function update(TicketUpdateRequest $request, $id)
    {
        $ticket =  parent::update($request, $id); // TODO: Change the autogenerated stub
        if($request->input('sync', false)) {
            $ticket = new Ticket(with($ticket, function (Ticket $ticket) {
                return $ticket->toArray();
            }));
            $ticket->exists = true;
            Event::fire(new SyncTicketCardInfoEvent($ticket, $ticket->cardInfo, app('wechat')->officeAccount()));
        }

        return $this->response()->item($ticket, new TicketTransformer());
    }

    /**
     * @param int $id
     * @return Response
     * @throws
     * */
    public function unavailable(int $id)
    {
        $result = $this->repository
            ->update(['status' => Ticket::UNAVAILABLE], $id);

        if($result) {
            $result = app('wechat')
                ->officeAccount()->card
                ->reduceStock($result->cardId, $result->cardInfo['base_info']['sku']['quantity']);

            if($result['errcode'] !== 0) {
                $this->response()->error('同步失败', HTTP_STATUS_NOT_MODIFIED);
            }else{
                return $this->response(new JsonResponse(['message' => '设置成功，卡券已经失效']));
            }
        }else{
            $this->response()->error('设置失败', HTTP_STATUS_NOT_MODIFIED);
        }
    }
}
