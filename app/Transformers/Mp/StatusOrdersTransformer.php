<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 15:45
 */

namespace App\Transformers\Mp;
use League\Fractal\TransformerAbstract;
use App\Entities\OrderItem;
use App\Entities\Order;


class StatusOrdersTransformer extends TransformerAbstract
{
    public function transform(Order $model){
        return [
            'id'      => $model->id,
            'code'    => $model->code,
            'write_code'     => buildUrl('api.mp','/confirm/order', $model->id),
            'type'    => $model->type,
            'status'  => $model->status,
            'receiver_name'    => $model->receiverName,
            'receiver_address' => isset(json_decode($model->receiverAddress)->receiver_address) ?json_decode($model->receiverAddress)->receiver_address : $model->receiverAddress ,
            'build_num'        => isset(json_decode($model->receiverAddress)->build_num) ? json_decode($model->receiverAddress)->build_num : null,
            'room_num'         => isset(json_decode($model->receiverAddress)->room_num) ? json_decode($model->receiverAddress)->room_num : null,
            'receiver_mobile'  => $model->receiverMobile,
            'quantity'          => $model->merchandiseNum,
            'total_amount'     => $model->totalAmount,
            'payment_amount'   => $model->paymentAmount,
            'shop_end_hour'    => isset($model->shop->end_at) ? $model->shop->end_at : null ,
            'created_at'       => $model->createdAt->format('Y-m-d H:i:s'),
            'order_item_merchandises' => $model->orderItems ? $model->orderItems->map(function (OrderItem $orderItem) {
                $data  = $orderItem->only(['name','sell_price','quality','total_amount','main_image']);
                return $data;
            }) : null,
        ];
    }
}