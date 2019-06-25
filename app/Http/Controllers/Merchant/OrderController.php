<?php
/**
 * Created by PhpStorm.
 * User: wangzaron
 * Date: 2019/6/25
 * Time: 10:24 PM
 */

namespace App\Http\Controllers\Merchant;


use App\Entities\Order;
use App\Entities\ShopManager;
use Dingo\Api\Http\Response;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function orders()
    {
        /**@var ShopManager $manager**/
        $manager = Auth::user();
        $orders  = $manager->orders()->where('paid_at', '>=', Carbon::now()->startOfDay())
            ->where('paid_at', '<', Carbon::now())->get();
        $orders = $orders->map(function (Order $order) {
            return [
                'pay_type' => $order->payTypeStr(),
                'pay_amount' => number_format($order->paymentAmount, 2),
                'paid_at' => $order->paidAt ? $order->paidAt->format('Y-m-d h:i:s')
                    : Carbon::now()->format('Y-m-d h:i:s')
            ];
        });
        /**@var Response $response**/
        $response = $this->response($orders);
        return $response;
    }
}