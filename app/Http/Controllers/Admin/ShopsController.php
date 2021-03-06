<?php

namespace App\Http\Controllers\Admin;

use App\Criteria\Admin\SearchRequestCriteria;
use App\Entities\App;
use App\Entities\Role;
use App\Entities\Shop;
use App\Entities\ShopManager;
use App\Entities\ShopMerchandise;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShopCreateRequest;
use App\Http\Requests\Admin\ShopUpdateRequest;
use App\Http\Response\JsonResponse;
use App\Repositories\SellerRepository;
use App\Repositories\ShopManagerRepository;
use App\Repositories\ShopMerchandiseRepository;
use App\Repositories\ShopRepository;
use App\Services\AppManager;
use App\Transformers\ShopItemTransformer;
use App\Transformers\ShopMerchandiseTransformer;
use App\Transformers\ShopTransformer;
use App\Utils\GeoHash;
use Dingo\Api\Http\Request;
use Exception;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as IlluminateRequest;
use Illuminate\Http\Response;
use Prettus\Repository\Criteria\RequestCriteria;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Class ShopsController.
 *
 * @package namespace App\Http\Controllers\Admin;
 */
class ShopsController extends Controller
{
    /**
     * @var ShopRepository
     */
    protected $repository;

    protected $sellerRepository;

    protected $shopManagerRepository;


    /**
     * ShopsController constructor.
     *
     * @param ShopRepository $repository
     * @param SellerRepository $sellerRepository
     * @param ShopManagerRepository $shopManagerRepository
     */
    public function __construct(ShopRepository $repository, SellerRepository $sellerRepository, ShopManagerRepository $shopManagerRepository)
    {
        $this->repository = $repository;
        $this->sellerRepository = $sellerRepository;
        $this->shopManagerRepository = $shopManagerRepository;
        parent::__construct();

    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->repository->pushCriteria(app(RequestCriteria::class));
        $this->repository->pushCriteria(app(SearchRequestCriteria::class));
        $shops = $this->repository->with(['country', 'province', 'city', 'county', 'shopManager'])
            ->withLastMonthAmount()
            ->withMerchandiseCount()
            ->withSellAmount()
            ->withOrderCount()
            ->withThisMonthAmount()
            ->paginate($request->input('limit', PAGE_LIMIT));
        return $this->response()->paginator($shops, new ShopItemTransformer());
    }

    /**
     * 获取店铺老板/经理人的用户信息
     * @param string $mobile
     * @param string $name
     * @return ShopManager
     * @throws
     * */
    protected function getManager(string $mobile, string $name)
    {
        $mobile = trim($mobile);
        $shopManager = $this->shopManagerRepository->findWhere(['mobile' => $mobile])->first();
        if (!$shopManager) {
            $shopManager = $this->shopManagerRepository->create([
                'user_name' => $mobile,
                'password' => password($mobile),
                'real_name' => $name,
                'mobile' => $mobile
            ]);
            $seller = $this->sellerRepository->find($shopManager->id);
            $role = Role::whereSlug(Role::SELLER)->first(['id']);
            $seller->roles()->attach($role->id);
        } else {
            $shopManager->userName = $mobile;
            $shopManager->realName = $name;
            $shopManager->password = password($mobile);
            $shopManager->mobile = $mobile;
            $shopManager->save();
        }
        return $shopManager;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ShopCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws Exception
     */
    public function store(ShopCreateRequest $request)
    {
        $data = $request->only(['code', 'name', 'country_id', 'province_id', 'city_id', 'county_id', 'address', 'description',
            'status']);
        $shopManager = $this->getManager($request->input('manager_mobile'), $request->input('manager_name'));
        $data['user_id'] = $shopManager->id;
        if ($request->input('lat', null) && $request->input('lng', null)) {
            $data['position'] = new Point($request->input('lat'), $request->input('lng'));
            $data['geo_hash'] = (new GeoHash())->encode($request->input('lat'), $request->input('lng'));
        }

        $shop = $this->repository->create($data);
        return $this->response()->item($shop, new ShopTransformer());
    }


    public function paymentQRCode(int $id, IlluminateRequest $request)
    {
        $shop = $this->repository->find($id);
        $size = $request->input('size', 200);
        if ($shop && $size !== null && $size > 0) {
            $url = webUriGenerator('/aggregate.html', env('WEB_PAYMENT_PREFIX'), env('WEB_DOMAIN'));
            $url .= "?shop_id={$shop->id}";
            $url .= "&selected_appid={$shop->appId}";
            $qrCode = QrCode::format('png')->size($size)->generate($url);
            if ($request->wantsJson()) {
                $qrCode = base64_encode($qrCode);
                return $this->response(new JsonResponse([
                    'qr_code' => 'data:image/png;base64, ' . $qrCode
                ]));
            } else {
                return Response::create($qrCode)->header('Content-Type', 'image/png');
            }
        } else {
            if ($request->wantsJson()) {
                return $this->response(new JsonResponse([
                    'message' => '失败 '
                ]));
            } else {
                return Response::create(['message' => '失败 ']);
            }
        }
    }

    public function officialAccountQRCode(int $id, IlluminateRequest $request)
    {
        $shop = $this->repository->find($id);
        if ($shop) {
            $url = $shop->wechatParamsQrcodeUrl;
            if (!$url) {
                $data = [
                    'app_id' => $shop->appId,
                    'shop_id' => $shop->id
                ];
                $currentApp = App::find($shop->appId);
                $result = app('wechat')->openPlatform()->officialAccount($currentApp->wechatAppId, $currentApp->officialAccount->authorizerRefreshToken)
                    ->qrcode->forever(base64_encode(json_encode($data)));
                if (!isset($result['ticket'])) {
                    throw new Exception('无法生成参数二维码');
                }
                $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={$result['ticket']}";
                $shop->wechatParamsQrcodeUrl = $url;
                $shop->save();
            }

            if ($request->wantsJson()) {
                return $this->response(new JsonResponse(['url' => $url]));
            } else {
                return redirect($url);
            }
        } else {
            return new Response('错误');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shop = $this->repository->with(['shopManager'])->find($id);
        return $this->response()->item($shop, new ShopTransformer());

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shop = $this->repository->find($id);

        return view('shops.edit', compact('shop'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ShopUpdateRequest $request
     * @param string $id
     *
     * @return Response
     *
     * @throws Exception
     */
    public function update(ShopUpdateRequest $request, $id)
    {
        $data = $request->only(['code', 'name', 'country_id', 'province_id', 'city_id', 'county_id',
            'address', 'description', 'status', 'user_id', 'start_at', 'end_at', 'manager_mobile', 'manager_name']);
        $appManager = app(AppManager::class);
        $data['app_id'] = $appManager->currentApp->id;
        if ($request->input('manager_mobile', null) && $request->input('manager_name', null))
            $data['user_id'] = $this->getManager($request->input('manager_mobile'), $request->input('manager_name'))->id;
        if ($request->input('lat', null) && $request->input('lng', null)) {
            $data['position'] = new Point($request->input('lat'), $request->input('lng'));
            $data['geo_hash'] = (new GeoHash())->encode($request->input('lat'), $request->input('lng'));
        }

        /** @var Shop $shop */
        $shop = $this->repository->update($data, $id);
        //$shop->shopManager->update(['mobile' => $data['manager_mobile'], 'nickname' => $data['manager_name']]);
        return $this->response()->item($shop, new ShopTransformer());
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->repository->delete($id);

//        if (request()->wantsJson()) {
//
//            return $this->response(new JsonResponse([
//                'message' => 'Shop deleted.',
//                'deleted' => $deleted,
//            ]));
//        }

        return $this->response(new JsonResponse([
            'message' => 'Shop deleted.',
            'deleted' => $deleted,
        ]));
        //return redirect()->back()->with('message', 'Shop deleted.');
    }

    public function merchandises(int $shopId, Request $request, ShopMerchandiseRepository $repository)
    {
        $shop = $this->repository->find($shopId);
        if ($shop) {
            $repository->pushCriteria(app(RequestCriteria::class));
            $repository->pushCriteria(app(SearchRequestCriteria::class));
            $items = $repository->with(['merchandise.categories'])->scopeQuery(function ($model) use ($shopId) {
                return $model->where('shop_id', $shopId);
            })->paginate($request->input('limit', PAGE_LIMIT));
            return $this->response()->paginator($items, new ShopMerchandiseTransformer());
        } else {
            throw new ModelNotFoundException('没有相应店铺信息');
        }
    }

    public function addMerchandise(int $shopId, Request $request)
    {
        $shop = $this->repository->find($shopId);
        if ($shop) {
            $merchandise = with($shop, function (Shop $shop) use ($request) {
                return $shop->shopMerchandises()->save(new ShopMerchandise($request->all()));
            });
            return $this->response()->item($merchandise, new ShopMerchandiseTransformer());
        } else {
            throw new ModelNotFoundException('没有相应店铺信息');
        }
    }

    public function payQRCode(int $storeId, Request $request)
    {
        $url = buildUrl('web.mpPayment', '/pay/{storeId}', ['storeId' => $storeId]);
        $code = app('qrcode')->format('png')->margin(2)->size($request->input('size', 128))->generate($url);
        return $this->response()->created()->setContent($code)->header('Content-Type', 'image/png');
    }

    public function __destruct()
    {
        $this->repository = null;
        $this->shopManagerRepository = null;
        $this->sellerRepository = null;
        parent::__destruct(); // TODO: Change the autogenerated stub
    }
}
