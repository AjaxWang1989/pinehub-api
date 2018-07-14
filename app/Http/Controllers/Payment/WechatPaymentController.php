<?php

namespace App\Http\Controllers\Payment;

use App\Entities\Customer;
use App\Entities\Order;
use App\Http\Controllers\Payment\PaymentController as Controller;
use App\Http\Response\JsonResponse;
use Dingo\Api\Http\Request as DingoRequest;
use Illuminate\Http\Request as LumenRequest;
use Dingo\Api\Http\Response;
use Payment\NotifyContext;
use App\Transformers\Api\PaymentSignedTransformer as WechatPaymentSigned;
use App\Entities\PaymentSigned as WechatPayment;

class WechatPaymentController extends Controller
{
    /**
     * 聚合支付
     * @param LumenRequest|DingoRequest $request
     * @return Response| null
     * @throws
     * */
    public function aggregate(LumenRequest $request)
    {
        $request->merge(['pay_type' => Order::WECHAT_PAY, 'type' => Order::OFF_LINE_PAY]);
        $order = $this->app->make('order.builder')->handle();
//        $charge = app('wechat.payment.aggregate');
        $result = app('wechat')->payment()->order->unify($order, $order->wechatAppId);
//        return $this->response()->item( new WechatPayment($this->preOrder($order->buildWechatAggregatePaymentOrder(), $charge)),
//            new WechatPaymentSigned());
        return $this->response(new JsonResponse($result));
    }

    public function aggregatePage(LumenRequest $request)
    {
        $paymentApi = paymentApiUriGenerator('/wechat/aggregate');
        $accept = "application/vnd.pinehub.v0.0.1+json";
        $config = app('wechat')->officeAccount()->jssdk->buildConfig(['chooseWXPay']);
        $openId = $request->input('open_id', null);
        $appId = $request->input('selected_appid', null);
        $customer = Customer::whereAppId($appId)
            ->wherePlatformOpenId($openId)
            ->first();

        try{
            $shop = $this->shopModel->find($request->input('shop_id'))
             $order = [
                 'type' => Order::OFF_LINE_PAY,
                 'pay_type' => Order::WECHAT_PAY,
                 'openid' => $openId,
                 'app_id' => $appId,
                 'wechat_app_id' => app('wechat')->officeAccount()->config['app_id'],
                 'buyer_id' => $customer->id,
                 'ip' => $request->getClientIp(),
                 'shop_id' => $shop->id
             ];;
            return view('payment.aggregate.wechatpay')->with([
                'shop' => $shop,
                'paymentApi' => $paymentApi,
                'config' => $config,
                'accept' => $accept,
                'order' => json_encode($order)
            ]);
        }catch (\Exception $exception) {
            $order = [
                'type' => Order::OFF_LINE_PAY,
                'pay_type' => Order::WECHAT_PAY,
                'openid' => $openId,
                'app_id' => $appId,
                'wechat_app_id' => app('wechat')->officeAccount()->config['app_id'],
                'buyer_id' => $customer->id,
                'ip' => $request->getClientIp()
            ];
            return view('payment.aggregate.wechatpay')->with([
                'paymentApi' => $paymentApi,
                'config' => $config,
                'accept' => $accept,
                'order' => json_encode($order)
            ]);
        }
    }


    public function notify(string $type = 'wechat', NotifyContext $notify = null)
    {
        $notify = $this->app->make('payment.wechat.notify');
        parent::notify($type, $notify); // TODO: Change the autogenerated stub
    }
}
