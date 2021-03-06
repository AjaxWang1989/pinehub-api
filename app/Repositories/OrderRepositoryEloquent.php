<?php /** @noinspection ALL */

namespace App\Repositories;

use App\Criteria\Admin\SearchRequestCriteria;
use App\Entities\OrderItem;
use App\Entities\ShopMerchandise;
use App\Repositories\Traits\Destruct;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Entities\Order;
use App\Validators\Api\OrderValidator;

/**
 * Class OrderRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class OrderRepositoryEloquent extends BaseRepository implements OrderRepository
{
    use Destruct;
    protected $fieldSearchable = [
        'type' => '=',
        'pay_type' => '=',
        'status' => '=',
        'customer_id' => '=',
        'member.mobile' => 'like',
        'orderItems.name' => 'like',
        'code' => 'like',
        'paid_at' => '*',
        'receiver_name' => 'like',
        'receiver_mobile' > 'like'
    ];
    protected $hourStartAt ;
    protected $hourEndAt;

    protected $weekStartAt;
    protected $weekEndAt;

    protected $montStartAt;
    protected $monthEndAt;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->hourStartAt = date('Y-m_d 00:00:00',time());
        $this->hourEndAt = date('Y-m-d 23:59:59',time());

        $this->weekStartAt = date('Y-m-d 00:00:00', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
        $this->weekEndAt = date('Y-m-d 23:59:59', (time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600));

        $this->montStartAt = date('Y-m-d 00:00:00', strtotime(date('Y-m', time()) . '-01 00:00:00'));
        $this->monthEndAt = date('Y-m-d 23:59:59', strtotime(date('Y-m', time()) . '-' . date('t', time()) . ' 00:00:00'));

    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }



    /**
     * Boot up the repository, pushing criteria
     * @throws
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
//        $this->pushCriteria(SearchRequestCriteria::class);

    }

    /**
     * @param array $itemMerchandises
     * @return bool
     */
    public function insertMerchandise(array $itemMerchandises)
    {
        $item = DB::table('order_items')->insert($itemMerchandises);
        return $item;
    }

    /**
     * 自提
     * @param string $date
     * @param int $shopId
     * @return mixed
     */

    public function storeBuffetOrders(string $date, int $shopId)
    {
        $this->scopeQuery(function (Order $order) use($shopId, $date) {
            return $order
                ->where(['shop_id' => $shopId])
                ->whereIn('status',[
                    Order::PAID,
                    Order::SEND,
                    Order::COMPLETED])
                ->where('send_date', $date)
                ->whereIn('type', [
                    Order::SHOPPING_MALL_ORDER,
                    Order::SITE_USER_ORDER
                ])->where('pick_up_method', Order::USER_SELF_PICK_UP)
                ->orderBy('id','desc');
        });
        return $this->paginate();
    }

    /**
     * 配送订单
     * @param string $date
     * @param int $batch
     * @param int $shopId
     * @return mixed
     */
    public function storeSendOrders(string $date, int $batch, int $shopId)
    {
        $this->scopeQuery(function (Order $order) use($shopId, $date, $batch) {
            return $order
                ->where(['shop_id' => $shopId])
                ->whereIn('status',[
                    Order::PAID,
                    Order::SEND,
                    Order::COMPLETED
                ])->where('send_date', $date)
                ->where('send_batch', $batch)
                ->whereIn('type', [
                    Order::SHOPPING_MALL_ORDER,
                    Order::SITE_USER_ORDER
                ])->where('pick_up_method', Order::SEND_ORDER_TO_USER)
                ->orderBy('id','desc');
        });
        return $this->paginate();
    }

    /**
     * @param string $status
     * @param int $customerId
     * @param int $limit
     * @return mixed
     */
    public function userOrders(string $status, int $customerId, int $limit = 15)
    {
        $where = [];
        if ($status == 'success'){
            $where = ['customer_id' => $customerId,'status' => Order::PAID];
        }elseif ($status == 'completed'){
            $where = ['customer_id' => $customerId,'status' => Order::COMPLETED];
        }elseif ($status == 'all'){
            $where = ['customer_id' => $customerId];
        }
        $this->scopeQuery(function (Order $order) use($where){
            return $order->where($where)
                ->whereIn('type', [Order::SITE_USER_ORDER, Order::OFF_LINE_PAYMENT_ORDER,
                    Order::SHOPPING_MALL_ORDER])
                ->orderBy('id','desc');
        });
        return $this->paginate($limit);
    }

    /**
     * @param array $request
     * @param int $shopId
     * @param string $limit
     * @return mixed
     */

    public function storeOrdersSummary($date, $type, $status, int $shopId)
    {
        $where = [];
        $startAt = $date.' 00:00:00';
        $endAt = $date.' 23:59:59';
        $this->scopeQuery(function (Order $order) use($where, $startAt, $endAt, $type, $shopId, $status) {
            $order = $order->where('shop_id', $shopId)
                ->where('paid_at', '>=', $startAt)
                ->where('paid_at', '<', $endAt);
            if ($status === 'undone') {
                $order = $order->whereIn('status', [Order::PAID, Order::SEND]);
            } elseif ($status === 'completed') {
                $order = $order->where('status', Order::COMPLETED);
            }
            if($type) {
                $order = $order->whereIn('type', [Order::SHOPPING_MALL_ORDER, Order::SITE_USER_ORDER]);
                if ($type) {
                    $order = $order->where('pick_up_method', $type);
                }
            }
            return $order;

        });
        return $this->paginate();
    }

    /**
     * 订单统计
     * @param int $shopId
     * @param string $unit
     * @return Collection
     */
    public function orderStatistics(int $shopId, string $unit, Carbon $startAt, Carbon $endAt, int $limit)
    {
        $this->scopeQuery(function (Order $order) use ($shopId, $unit, $startAt, $endAt, $limit) {
            return $order->select([
                $unit,
                DB::raw('sum( `payment_amount` ) as total_payment_amount'),
                'type',
                DB::raw('count(*) as order_count'),
                DB::raw('sum( `merchandise_num` ) as merchandise_count'),
                'paid_at'])
                ->whereIn('status', [Order::PAID, Order::SEND, Order::COMPLETED])
                ->where(['shop_id' => $shopId])
                ->where('paid_at', '>=', $startAt)
                ->where('paid_at', '<', $endAt)
                ->whereIn('type', [Order::SHOPPING_MALL_ORDER, Order::SITE_USER_ORDER, Order::OFF_LINE_PAYMENT_ORDER])
                ->groupby($unit)
                ->orderBy($unit, 'desc')
                ->limit($limit);
        });
        $orders = $this->get();
        return $orders;
    }

    public function buildOrderStatisticData(Collection $orders, $count, $unit)
    {
        $statisticsData = [ ];
        //循环组装当前截止时间的数据
        for ($i = 0; $i < $count ; $i++){
            $statisticsData[$i] = 0;
            $orders->map(function ($order, $index) use($statisticsData, $unit, $i){
                if($order[$unit] === $i + 1){
                    $statisticsData[$i] = $order['total_payment_amount'];
                }
            });
        }
        //预定产品金额总和
        $bookPaymentAmount = $orders
            ->where('type', Order::SHOPPING_MALL_ORDER)
            ->sum('total_payment_amount');
        //站点产品金额总和
        $sitePaymentAmount = $orders
            ->where('type', Order::SITE_USER_ORDER)
            ->sum('total_payment_amount');
        //销售单品数量总和
        $sellMerchandiseNum = $orders->sum('merchandise_count');
        //销售笔数
        $sellOrderNum = $orders->sum('order_count');
        return [
            'order_statistics' => $statisticsData,
            'total_order_amount' => $bookPaymentAmount + $sitePaymentAmount,
            'booking_order_total_payment_amount' => $bookPaymentAmount,
            'store_order_total_payment_amount' => $sitePaymentAmount,
            'sell_merchandise_num' => $sellMerchandiseNum,
            'order_total_num' => $sellOrderNum
        ];
    }

    /**
     * @param int $activityId
     * @param int $customerId
     * @param string $limit
     * @return mixed
     */
    public function activityUsuallyReceivingStores(int $activityId, int $customerId, int $limit = 3)
    {
        $this->scopeQuery(function (Order $order) use($activityId , $customerId){
            return $order
                ->where('customer_id', $customerId)
                ->where('activity_id', $activityId)
                ->groupBy('receiving_shop_id');
        });
        return $this->paginate($limit);
    }
}
