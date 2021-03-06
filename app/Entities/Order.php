<?php /** @noinspection ALL */

namespace App\Entities;

use App\Entities\Traits\ModelAttributesAccess;
use App\Events\OrderPaidEvent;
use App\Events\OrderPaidNoticeEvent;
use App\Jobs\OrderUpdateStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\Order
 *
 * @property int $id
 * @property string $code 订单编号
 * @property string|null $openId 微信open id或支付宝user ID
 * @property string|null $wechatAppId 维系app id
 * @property string|null $aliAppId 支付宝app id
 * @property string|null $appId 系统app id
 * @property int|null $shopId 店铺id
 * @property int|null $activityId 新品活动id
 * @property int|null $memberId 买家会员id
 * @property string $cardId 优惠券id
 * @property string $cardCode 优惠券code
 * @property int|null $customerId 买家
 * @property int|null $merchandiseNum 此订单商品数量总数
 * @property float $totalAmount 应付款
 * @property float $paymentAmount 实际付款
 * @property float $discountAmount 优惠价格
 * @property \Illuminate\Support\Carbon|null $paidAt 支付时间
 * @property int $payType 支付方式默认微信支付:0-未知，1-支付宝，2-微信支付
 * @property int $status 订单状态：0-订单取消 100-等待提交支付订单 200-提交支付订单 300-支付完成 400-已发货 500-订单完成 600-支付失败
 * @property int $cancellation 取消人 0未取消 1买家取消 2 卖家取消  3系统自动取消
 * @property string $sendDate 配送日期
 * @property int $sendBatch 配送批次
 * @property \Illuminate\Support\Carbon|null $signedAt 签收时间
 * @property string|null $receiverCity 收货城市
 * @property string|null $receiverDistrict 收货人所在城市区县
 * @property string|null $receiverName 收货姓名
 * @property string|null $receiverAddress 收货地址
 * @property string|null $receiverMobile 收货人电话
 * @property string|null $comment 备注
 * @property \Illuminate\Support\Carbon|null $consignedAt 发货时间
 * @property int $type 订单类型：0-线下扫码 1-商城订单 2-站点用户订单  3-商家进货订单
 * @property int $pickUpMethod 取货方式：0-不需要取货 1-送货上门 2-自提
 * @property int $postType 0-无需物流，1000 - 未知运输方式 2000-空运， 3000-公路， 4000-铁路， 5000-高铁， 6000-海运
 * @property int $scoreSettle 积分是否已经结算
 * @property string|null $postNo 快递编号
 * @property string|null $postCode 邮编
 * @property string|null $postName 快递公司名称
 * @property string|null $transactionId 支付交易流水
 * @property string|null $ip 支付终端ip地址
 * @property string|null $tradeStatus 交易状态:TRADE_WAIT 等待交易 TRADE_FAILED 交易失败 TRADE_SUCCESS 交易成功
 *                 TRADE_FINISHED 交易结束禁止退款操作 TRADE_CANCEL 交易关闭禁止继续支付
 * @property string $prepayId
 * @property int|null $year 年
 * @property int|null $month 月
 * @property int|null $day 日
 * @property int|null $week 星期
 * @property int|null $hour 小时
 * @property int|null $receivingShopId 收货店铺id
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property string|null $deletedAt
 * @property-read \App\Entities\Activity|null $activity
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entities\ActivityMerchandise[] $activityMerchandises
 * @property-read \App\Entities\Customer|null $customer
 * @property-read \App\Entities\CustomerTicketCard $customerTicket
 * @property-read \App\Entities\Member|null $member
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entities\OrderItem[] $orderItems
 * @property-read \App\Entities\Shop|null $receivingShopAddress
 * @property-read \App\Entities\Shop|null $shop
 * @property-read \App\Entities\Card $tickets
 * @property-read \App\Entities\UserRechargeableCardConsumeRecord|null $consumeRecord
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereAliAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCancellation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCardCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereConsignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereMerchandiseNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereOpenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePaymentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePickUpMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePostName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePostNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order wherePostType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceiverAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceiverCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceiverDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceiverMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceiverName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereReceivingShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereScoreSettle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereSendBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereSendDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereTradeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereWechatAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Order whereYear($value)
 * @mixin \Eloquent
 */
class Order extends Model implements Transformable
{
    use TransformableTrait, ModelAttributesAccess;
    const CANCEL = 0;// 关闭
    const WAIT = 100;// 等待提交
    const MAKE_SURE = 200;// 等待支付
    const PAID = 300;// 完成支付
    const SEND = 400;// 已经配送
    const COMPLETED = 500;// 支付完成
    const PAY_FAILED = 600;// 支付失败

    const ORDER_NUMBER_PREFIX = 'PH';

    const UNKOWN_PAY = 0;
    const ALI_PAY = 1;
    const WECHAT_PAY = 2;
    const BALANCE_PAY = 3;

    // 取货方式
    const NOT_NEED_PICK_UP_METHOD = 0;
    const SEND_ORDER_TO_USER = 1;// 送货上门
    const USER_SELF_PICK_UP = 2; // 自提


    // 订单类型：0-线下扫码 1-预定自提 2-商城订单 3-今日下单自提 4-今日下单送到手  5-商家进货订单
//    const OFF_LINE_PAY = 0;
//    const ORDERING_PAY = 1;
//    const E_SHOP_PAY =2;
//    const SITE_SELF_EXTRACTION = 3;
//    const SITE_DISTRIBUTION = 4;

    // 订单类型：0-线下扫码 1-商城订单 2-站点用户订单  3-商家进货订单 4 -充值
    const OFF_LINE_PAYMENT_ORDER = 0;

    const SHOPPING_MALL_ORDER = 1;

    const SITE_USER_ORDER = 2;

    const SHOP_PURCHASE_ORDER = 3;

    const CHARGE_BALANCE = 4;

    const EXPIRES_SECOND = 600;

    const VIRTRUAL_MERCHANDISE = 0;
    const REAL_MERCHANDISE = 1;

    const TRADE_WAIT = 'TRADE_WAIT';
    const TRADE_FAILED = 'TRADE_FAILED';
    const TRADE_SUCCESS = 'TRADE_SUCCESS';
    const TRADE_FINISHED = 'TRADE_FINISHED';
    const TRADE_CANCEL = 'TRADE_CANCEL';

    protected $dates = [
        'signed_at',
        'consigned_at',
        'paid_at'
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'customer_id', 'card_id', 'card_code', 'merchandise_num', 'total_amount',
        'payment_amount', 'discount_amount', 'paid_at', 'pay_type', 'status', 'cancellation',
        'signed_at', 'consigned_at', 'post_no', 'post_code', 'post_name', 'receiver_city',
        'receiver_district', 'receiver_name', 'receiver_address', 'receiver_mobile',
        'comment', 'type', 'app_id', 'open_id', 'wechat_app_id', 'ali_app_id',
        'score_settle', 'ip', 'open_id', 'transaction_id', 'shop_id', 'member_id', 'trade_status',
        'year', 'month', 'week', 'hour', 'activity_id', 'receiving_shop_id', 'send_batch', 'day',
        'pick_up_method', 'pick_up_start_time', 'pick_up_end_time', 'prepay_id'
    ];

    public static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        Order::creating(function (Order &$order) {
            $order->code = app('uid.generator')->getUid(ORDER_CODE_FORMAT, ORDER_SEGMENT_MAX_LENGTH);
            return $order;
        });

        Order::saved(function (Order &$order) {
            if ($order->getOriginal('status') !== $order->status) {
                $order->updateOrderItemStatus();
                if (Order::CANCEL === $order->status) {
                    $order->updateStock();
                }

                if (Order::PAID === $order->status) {
                    $order->useTicket();
                }

                if ($order->status === Order::PAID || $order->status === Order::COMPLETED) {
                    Log::info('================订单已付款，触发订单支付完成时间==============');
                    event(new OrderPaidEvent($order));
                }

                if (Order::WAIT === $order->status) {
                    $date = Carbon::now()->addMinute(config('order.auto_cancel_time'));
                    $job = (new OrderUpdateStatus($order->id, Order::CANCEL))
                        ->delay($date);
                    dispatch($job);
                }

                if ((Order::PAID === $order->status && $order->type === Order::SITE_USER_ORDER)
                    || (Order::SEND === $order->status && $order->type === Order::SHOPPING_MALL_ORDER)) {
                    $date = Carbon::now()->addDay(config('order.trade_finished_time'));
                    $job = (new OrderUpdateStatus($order->id, Order::COMPLETED))
                        ->delay($date);
                    dispatch($job);
                }

                Log::info('======== order ========', [$order->status, $order->type]);
                if (Order::COMPLETED === $order->status && $order->type === Order::OFF_LINE_PAYMENT_ORDER) {
                    Log::info('------- order off line paid ----------');
                    event(new OrderPaidNoticeEvent($order->shopId, $order));
                }
            }
        });
    }

    public function useTicket()
    {
        if ($this->cardCode) {
            $this->customerTicket()->update([
                'status' => CustomerTicketCard::STATUS_USE
            ]);
        }
    }

    public function updateStock()
    {
        if ($this->shopId && !$this->activityId) {
            $this->updateShopMerchandises();
        } elseif ($this->activityId) {
            $this->updateActivityMerchandises();
        } else {
            $this->updateMerchandises();
        }
    }

    public function updateOrderItemStatus()
    {
        $this->orderItems()
            ->update([
                'status' => $this->status,
                'paid_at' => $this->paidAt
            ]);
    }

    public function updateMerchandises()
    {
        $orderItems = $this->orderItems;
        $orderItems->each(function (OrderItem $orderItem) use (&$merchandises) {
            $orderItem->merchandise->stockNum += $orderItem->quality;
            $orderItem->merchandise->sellNum -= $orderItem->quality;
            $orderItem->merchandise->save();
        });


    }

    public function updateActivityMerchandises()
    {
        $orderItems = $this->orderItems;
        $merchandiseIds = [];
        //获取店铺产品id放入id数组中
        $orderItems->each(function (OrderItem $item) use (&$merchandiseIds) {
            array_push($merchandiseIds, $item->merchandiseId);
        });
        $merchandises = $this->activityMerchandises()
            ->whereIn('merchandise_id', $merchandiseIds)
            ->get();

        //修改查询到的商品的库存
        $merchandises->each(function (ActivityMerchandise $merchandise) use (&$orderItems) {
            $orderItem = $orderItems->where('merchandise_id', $merchandise->merchandiseId)
                ->first();
            $merchandise->stockNum += $orderItem->quality;
            $merchandise->sellNum -= $orderItem->quality;
        });

        //通过活动商品关系保存修改过库存的商品
        $this->activityMerchandises()->saveMany($merchandises);
    }

    public function updateShopMerchandises()
    {
        $orderItems = $this->orderItems;
        $shop = $this->shop;
        $merchandiseIds = [];
        //获取店铺产品id放入id数组中
        $orderItems->each(function (OrderItem $item) use (&$merchandiseIds) {
            array_push($merchandiseIds, $item->merchandiseId);
        });

        //通过店铺中的产品关系查询符合符合条件的产品
        $merchandises = $shop->shopMerchandises()
            ->whereIn('merchandise_id', $merchandiseIds)
            ->get();

        //修改查询到的商品的库存
        $merchandises->each(function (ShopMerchandise $merchandise) use (&$orderItems) {
            $orderItem = $orderItems->where('merchandise_id', $merchandise->merchandiseId)
                ->first();
            $merchandise->stockNum += $orderItem->quality;
            $merchandise->sellNum -= $orderItem->quality;
        });

        //通过店铺商品关系保存修改过库存的商品
        $shop->shopMerchandises()
            ->saveMany($merchandises);
    }

    public function activityMerchandises(): hasMany
    {
        return $this->activity->merchandises();
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }


    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function tickets(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id', 'card_id');
    }

    public function customerTicket(): BelongsTo
    {
        return $this->belongsTo(CustomerTicketCard::class, 'card_code', 'card_code');
    }

    public function receivingShopAddress(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'receiving_shop_id', 'id');
    }

    public function buildAliWapPaymentOrder()
    {
        $now = Carbon::now();
        $expire = $now->addSeconds(self::EXPIRES_SECOND);
        $clientIp = app('request')->getClientIp();
        return [
            'body' => 'PineHub offline scan qrcode pay',
            'subject' => '支付宝手机网站支付',
            'order_no' => $this->code,
            'timeout_express' => $expire->timestamp, // 表示必须 600s 内付款
            'amount' => $this->paymentAmount, // 单位为元 ,最小为0.01
            'return_param' => 'tata', // 一定不要传入汉字，只能是 字母 数字组合
            'client_ip' => $clientIp,// 客户地址
            'goods_type' => self::REAL_MERCHANDISE,// 0—虚拟类商品，1—实物类商品
            'store_id' => '',
            'quit_url' => '', // 收银台的返回按钮（用户打断支付操作时返回的地址,4.0.3版本新增）
        ];
    }

    public function buildAliAggregatePaymentOrder()
    {
        $now = Carbon::now();
        $expire = $now->addSeconds(self::EXPIRES_SECOND);
        $request = app('request');
        $clientIp = $request->getClientIp();
        return [
            'body' => 'ali qr pay',
            'subject' => '支付宝扫码支付',
            'order_no' => $this->code,
            'timeout_express' => $expire->timestamp,// 表示必须 600s 内付款
            'amount' => $this->paymentAmount,// 单位为元 ,最小为0.01
            'client_ip' => $clientIp,// 客户地址
            'goods_type' => self::REAL_MERCHANDISE,// 0—虚拟类商品，1—实物类商品
            'store_id' => '',
            'operator_id' => '',
            'terminal_id' => '',// 终端设备号(门店号或收银设备ID) 默认值 web
            'buyer_id' => $this->openId
        ];
    }

    public function buildWechatWapPaymentOrder()
    {
        $now = Carbon::now();
        $expire = $now->addSeconds(self::EXPIRES_SECOND);
        $clientIp = app('request')->getClientIp();
        return [
            'body' => 'PineHub offline scan qrcode pay',
            //'subject'    => '微信扫码支付',
            'order_no' => $this->code,
            'timeout_express' => $expire->timestamp,// 表示必须 600s 内付款
            'amount' => $this->paymentAmount,// 微信沙箱模式，需要金额固定为3.01
            'return_param' => '123',
            'client_ip' => $clientIp,// 客户地址
            // 如果是服务商，请提供以下参数
            'sub_appid' => '',//微信分配的子商户公众账号ID
            'sub_mch_id' => '',// 微信支付分配的子商户号
        ];
    }

    public function buildWechatAggregatePaymentOrder()
    {
        $now = Carbon::now();
        $expire = $now->addSeconds(self::EXPIRES_SECOND);
        $request = app('request');
        $clientIp = $request->getClientIp();
        return [
            'body' => 'PineHub offline scan qrcode pay',
            'subject' => '线下扫码支付',
            'order_no' => $this->code,
            'timeout_express' => $expire->timestamp,// 表示必须 600s 内付款
            'amount' => $this->paymentAmount,// 微信沙箱模式，需要金额固定为3.01
            'return_param' => base64_encode(json_encode([
                'id' => $this->id,
                'order_no' => $this->code
            ])),
            'client_ip' => $clientIp,// 客户地址
            'openid' => $this->openId,
        ];
    }

    public function scopeWhereShopId(Builder $query, int $shopId)
    {
        return $query->whereHas('orderItems', function (Builder $query) use ($shopId) {
            return $query->where('shop_id', $shopId);
        });
    }

    public function payTypeStr()
    {
        switch ($this->payType) {
            case self::WECHAT_PAY:
                return '微信支付';
                break;
            case self::ALI_PAY:
                return '支付宝支付';
                break;
            case self::BALANCE_PAY:
                return '余额支付';
                break;
            case self::UNKOWN_PAY:
                return '其他支付方式';
                break;
        }
    }

    public function orderTypeStr()
    {
        if ($this->type === self::OFF_LINE_PAYMENT_ORDER) {
            return '聚合支付';
        }
        switch ($this->pickUpMethod) {
            case self::USER_SELF_PICK_UP:
                return '预订自提';
                break;
            case self::SEND_ORDER_TO_USER:
                return '送货上门';
                break;
        }
    }

    public function consumeRecord()
    {
        return $this->hasOne(UserRechargeableCardConsumeRecord::class, 'order_id');
    }
}
