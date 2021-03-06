<?php

namespace App\Http\Controllers\Admin;

use App\Criteria\Admin\SearchRequestCriteria;
use App\Entities\Merchandise;
use App\Entities\RechargeableCard;
use App\Exceptions\HttpValidationException;
use App\Http\Controllers\Controller;
use App\Repositories\MerchandiseRepository;
use App\Repositories\RechargeableCardRepository;
use App\Transformers\RechargeableCardTransformer;
use App\Validators\Admin\RechargeableCardValidator;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;

class RechargeableCardController extends Controller
{
    /**
     * @var RechargeableCardRepository $repository
     */
    private $repository;

    /**
     * @var RechargeableCardValidator $validator
     */
    private $validator;

    public function __construct(RechargeableCardRepository $repository, RechargeableCardValidator $validator)
    {
        $this->repository = $repository;

        $this->validator = $validator;

        parent::__construct();
    }

    /**
     * 卡种列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(app(RequestCriteria::class));

        $this->repository->pushCriteria(app(SearchRequestCriteria::class));

        $rechargeableCards = $this->repository->orderBy('created_at', 'desc')->paginate($request->input('limit', PAGE_LIMIT));

        return $this->response()->paginator($rechargeableCards, new RechargeableCardTransformer);
    }

    // 详情
    public function show(int $id)
    {
        $rechargeCard = $this->repository->find($id);

        return $this->response()->item($rechargeCard, new RechargeableCardTransformer);
    }

    /** 新建
     * @param Request $request
     * @param MerchandiseRepository $merchandiseRepository
     * @return Response
     */
    public function store(Request $request, MerchandiseRepository $merchandiseRepository)
    {
        $postData = $request->post();

        try {
            $this->validator->with($postData)->passesOrFail(ValidatorInterface::RULE_CREATE);
            $merchandiseData = [
                'name' => $postData['name'],
                'main_image' => '',
                'images' => '',
                'preview' => '',
                'detail' => '',
                'origin_price' => $postData['price'],
                'sell_price' => $postData['price'],
                'cost_price' => $postData['price'],
                'factory_price' => $postData['price'],
                'status' => in_array($postData['status'], [RechargeableCard::STATUS_ON, RechargeableCard::STATUS_PREFERENTIAL]) ? Merchandise::UP : Merchandise::DOWN,
            ];
            $merchandise = $merchandiseRepository->create($merchandiseData);
            tap($merchandise, function (Merchandise $merchandise) use ($postData) {
                $merchandise->categories()->sync([$postData['category_id']]);
            });
            $postData['merchandise_id'] = $merchandise->id;
            $rechargeableCard = $this->repository->create($postData);
        } catch (ValidatorException $exception) {
            throw new HttpValidationException($exception->getMessageBag());
        }

        return $this->response()->item($rechargeableCard, new RechargeableCardTransformer);
    }

    /** 修改 仅允许修改 推荐位/优惠态/排序/状态
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id)
    {
        $postData = $request->only(['is_recommend', 'on_sale', 'sort', 'status']);

        try {
            $this->validator->with($postData)->passesOrFail(ValidatorInterface::RULE_UPDATE);
            /** @var RechargeableCard $rechargeableCard */
            $rechargeableCard = $this->repository->update($postData, $id);
            $merchandiseDataNeedUpdate = [];
            if (isset($postData['status'])) {
                if (in_array($postData['status'], [RechargeableCard::STATUS_ON, RechargeableCard::STATUS_PREFERENTIAL])) {
                    $merchandiseDataNeedUpdate['status'] = Merchandise::UP;
                }
                if ($postData['status'] === RechargeableCard::STATUS_OFF) {
                    $merchandiseDataNeedUpdate['status'] = Merchandise::DOWN;
                }
            }
            if (isset($postData['on_sale'])) {
                if ($postData['on_sale']) {
                    $price = $rechargeableCard->preferentialPrice / 100;
                    $rechargeableCard->status = RechargeableCard::STATUS_PREFERENTIAL;
                } else {
                    $price = $rechargeableCard->price / 100;
                    $rechargeableCard->status = RechargeableCard::STATUS_ON;
                }
                $merchandiseDataNeedUpdate['origin_price'] = $price;
                $merchandiseDataNeedUpdate['sell_price'] = $price;
                $merchandiseDataNeedUpdate['cost_price'] = $price;
                $merchandiseDataNeedUpdate['factory_price'] = $price;
            }
            $rechargeableCard->update();
            $rechargeableCard->merchandise()->update($merchandiseDataNeedUpdate);
        } catch (ValidatorException $exception) {
            throw new HttpValidationException($exception->getMessageBag());
        }

        return $this->response()->item($rechargeableCard, new RechargeableCardTransformer);
    }

    /** 删除
     * @param int $id
     * @return Response
     */
    public function delete(int $id)
    {
        $this->repository->delete($id);

        return $this->response()->noContent();
    }
}
