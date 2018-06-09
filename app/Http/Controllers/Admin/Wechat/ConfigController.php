<?php

namespace App\Http\Controllers\Admin\Wechat;

use App\Http\Requests\Admin\Wechat\ConfigCreateRequest;
use App\Http\Requests\Admin\Wechat\ConfigUpdateRequest;
use App\Http\Response\JsonResponse;
use App\Repositories\WechatConfigRepository;
use App\Transformers\WechatConfigItemTransformer;
use App\Transformers\WechatConfigTransformer;
use Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Http\Response;

class ConfigController extends Controller
{
    /**
     * @var MaterialsRepository
     */
    protected $repository;


    /**
     * MaterialsController constructor.
     *
     * @param WechatConfigRepository $repository
     */
    public function __construct(WechatConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @return Response|DingoResponse
     */
    public function index()
    {
        $materials = $this->repository->paginate();

        if (request()->wantsJson()) {

            return $this->response()->paginator($materials, new WechatConfigItemTransformer());
        }

        return view('materials.index', compact('materials'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ConfigCreateRequest $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws
     */
    public function store(ConfigCreateRequest $request)
    {
        try {

            $material = $this->repository->create($request->all());

            $response = [
                'message' => '成功创建小程序或者公众号配置信息.',
            ];

            if ($request->wantsJson()) {

                return $this->response()->item($material, new WechatConfigTransformer());
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidationHttpException $e) {
            if ($request->wantsJson()) {
                return $this->response()->error($e->getMessageBag());
            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
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
        $material = $this->repository->find($id);

        if (request()->wantsJson()) {

            return $this->response()->item($material, new WechatConfigTransformer());
        }

        return view('materials.show', compact('material'));
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
        $material = $this->repository->find($id);

        return view('materials.edit', compact('material'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ConfigUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     * @throws
     */
    public function update(ConfigUpdateRequest $request, $id)
    {
        try {

            $material = $this->repository->update($request->all(), $id);

            $response = [
                'message' => '小程序或者公众号信息修改成功.',
            ];

            if ($request->wantsJson()) {

                return $this->response()->item($material, new WechatConfigTransformer());
            }

            return redirect()->back()->with('message', $response['message']);
        } catch (ValidationHttpException $e) {

            if ($request->wantsJson()) {

                return $this->response()->error($e->getMessageBag());
            }

            return redirect()->back()->withErrors($e->getMessageBag())->withInput();
        }
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
        $message = "删除指定配置信息。";
        if (request()->wantsJson()) {

            return $this->response(new JsonResponse([
                'message' => $message,
                'deleted' => $deleted,
            ]));
        }

        return redirect()->back()->with('message', $message);
    }
}