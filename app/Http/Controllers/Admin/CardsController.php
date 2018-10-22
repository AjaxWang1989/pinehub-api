<?php

namespace App\Http\Controllers\Admin;

use App\Criteria\Admin\CardCriteria;
use App\Entities\Card;
use App\Entities\Ticket;
use App\Events\SyncTicketCardInfoEvent;
use App\Http\Response\JsonResponse;

use App\Services\AppManager;
use Dingo\Api\Http\Response;
use Exception;
use App\Http\Requests\Admin\CardCreateRequest;
use App\Http\Requests\Admin\CardUpdateRequest;
use App\Transformers\CardTransformer;
use App\Transformers\CardItemTransformer;
use App\Repositories\CardRepository;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;

/**
 * Class CardsController.
 *
 * @package namespace App\Http\Controllers\Admin;
 */
class CardsController extends Controller
{
    /**
     * @var CardRepository
     */
    protected $repository;


    /**
     * CardsController constructor.
     *
     * @param CardRepository $repository
     */
    public function __construct(CardRepository $repository)
    {
        $this->repository = $repository;
        parent::__construct();
        $this->repository->pushCriteria(CardCriteria::class);
    }

    public function colors()
    {
        return $this->wechat->officeAccount()->card->colors();
    }

    public function  categories()
    {
        return $this->wechat->officeAccount()->card->categories();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cards = $this->repository->paginate();
        return $this->response()->paginator($cards, new CardItemTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CardCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws Exception
     */
    public function store(CardCreateRequest $request)
    {
        $appManager = app(AppManager::class);
        $data['card_info'] = $request->input('ticket_info');
        $data['app_id'] = $appManager->currentApp->id;
        $data['wechat_app_id'] = $appManager->currentApp->wechatAppId;
        $data['begin_at'] = $request->input('begin_at');
        $data['end_at'] = $request->input('end_at');
        $data['card_type'] = $request->input('ticket_type');
        $card = $this->repository->create($data);
        if ($request->input('sync', false)) {
            $ticket = new Ticket(with($card, function (Card $card) {
                return $card->toArray();
            }));
            $ticket->exists = true;
            Event::fire(new SyncTicketCardInfoEvent($ticket, [], app('wechat')->officeAccount()));
        }
        return $this->response()->item($card, new CardTransformer());
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $card = $this->repository->find($id);
        return $this->response()->item($card, new CardTransformer());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $card = $this->repository->find($id);

        return view('cards.edit', compact('card'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CardUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws Exception
     */
    public function update(CardUpdateRequest $request, $id)
    {
       $data['card_type'] = $request->input('ticket_type');
       $data['card_info'] = $request->input('ticket_info');
       $data['begin_at'] = $request->input('begin_at', null);
       $data['end_at'] = $request->input('end_at', null);
       $card = $this->repository->find($id);
       tap($card, function (Card $card) use($data){
          $card->cardInfo = multi_array_merge($card->cardInfo, $data['card_info']);
          $card->cardType = $data['card_type'];
          $card->beginAt  = $data['begin_at'];
          $card->endAt    = $data['end_at'];
          $card->save();
       });
       if($request->input('sync', false)) {
           $ticket = new Ticket(with($card, function (Card $card) {
               return $card->toArray();
           }));
           $ticket->exists = true;
           Event::fire(new SyncTicketCardInfoEvent($ticket, $data['card_info'], app('wechat')->officeAccount()));
       }
       return $this->response()->item($card, new CardTransformer());
    }

    /**
     * @param int $id
     * @return Response
     * @throws
     * */
    public function unavailable(int $id)
    {
        $result = $this->repository->update(['status' => Ticket::UNAVAILABLE], $id);
        if($result) {
            $result = app('wechat')->officeAccount()->card->reduceStock($result->cardId, $result->cardInfo['base_info']['sku']['quantity']);
            if($result['errcode'] !== 0) {
                $this->response()->error('同步失败', HTTP_STATUS_NOT_MODIFIED);
            }else{
                return $this->response(new JsonResponse(['message' => '设置成功，卡券已经失效']));
            }
        }else{
            $this->response()->error('设置失败', HTTP_STATUS_NOT_MODIFIED);
        }
    }

    public function qrCode(int $id)
    {
        $ticket = $this->repository->find($id);

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

        if (request()->wantsJson()) {

            return $this->response(new JsonResponse([
                'message' => 'Card deleted.',
                'deleted' => $deleted,
            ]));
        }

        return redirect()->back()->with('message', 'Card deleted.');
    }
}
