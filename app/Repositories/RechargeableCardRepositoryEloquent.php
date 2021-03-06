<?php

namespace App\Repositories;

use App\Entities\Customer;
use App\Entities\RechargeableCard;
use App\Entities\UserRechargeableCard;
use App\Validators\Admin\RechargeableCardValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class RechargeableCardRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class RechargeableCardRepositoryEloquent extends BaseRepository implements RechargeableCardRepository
{
    protected $fieldSearchable = [
        'status' => '=',
        'price' => 'between',
        'discount' => 'between',
        'category_id' => '=',
        'card_type' => '=',
        'type' => '=',
        'on_sale' => '=',
        'is_recommend' => '=',
        'name' => 'like'
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return RechargeableCard::class;
    }

    /**
     * Specify Validator class name
     *
     * @return mixed
     */
    public function validator()
    {

        return RechargeableCardValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * 卡片列表
     * 如果参数中有amount（实际付款金额），返回用户余额（balance，通过计算得到）和推荐列表，
     * 否则返回正常储值列表，若参数中有card_type（卡片类型），则返回相应类型卡片，否则，返回全部卡片
     * @param Customer $customer 小程序用户
     * @param array $conditions 查询参数
     * @return array 余额&推荐 | Collection<RechargeableCard>
     */
    public function getList(Customer $customer, array $conditions): array
    {
        $amount = request()->get('amount', null);
        $cardType = request()->get('card_type', null);
        $cardTypes = array_keys(RechargeableCard::CARD_TYPES);

        if ($cardType && is_string($cardType)) {
            $cardType = ucwords($cardType);
        }
        if ($cardType && !in_array($cardType, $cardTypes)) {
            throw new InvalidArgumentException("参数card_type错误，应为字符串 " . implode('或', $cardTypes) . " 其中一种");
        }

        if (!is_null($amount)) {
            $balance = 0;
            $user = $customer->member;
            if ($user) {
                $balance += $user->balance;// 用户账户余额

                // 可用余额
                // 用户持有的有效储蓄卡卡种
                $userRechargeableCards = $user->rechargeableCardRecords()->with([
                    'rechargeableCard' => function ($query) {
                        $query->where('card_type', RechargeableCard::CARD_TYPE_DEPOSIT);
                    }
                ])->where('status', '=', UserRechargeableCard::STATUS_VALID)->orderBy('created_at', 'asc')->get();

                $limitCard = false;
                $today = Carbon::now();
                /** @var UserRechargeableCard $userRechargeableCard */
                foreach ($userRechargeableCards as $userRechargeableCard) {
                    $rechargeableCard = $userRechargeableCard->rechargeableCard;
                    if ($rechargeableCard->type === RechargeableCard::TYPE_INDEFINITE) {
                        $balance += $userRechargeableCard->amount / 100;
                    } else if (!$limitCard && $today->gte($userRechargeableCard->validAt->startOfDay()) && $today->lte($userRechargeableCard->invalidAt->startOfDay())) {
                        $balance += $userRechargeableCard->amount / 100;
                        $limitCard = true;
                    }
                }
            }

            $balance = number_format($balance, 2);

            $priceDisparity = $amount - $balance;// 差价，基于差价选择推荐卡种

            $rechargeableCards = $this->scopeQuery(function (RechargeableCard $rechargeableCard) use ($priceDisparity, $cardType) {
                /*
                 * 选择卡种：卡内金额大于等于差价 且 唯一性无限期储值
                 */
                return $rechargeableCard->active()->where(function (Builder $query) use ($priceDisparity, $cardType) {
                    if ($priceDisparity > 0) {
                        $query->where('amount', '>=', $priceDisparity * 100);
                    }
                    if ($cardType) {
                        $query->where('card_type', $cardType);
                    }
                });
            })->all();
        } else {
            $indefiniteCard = $customer->indefiniteRechargeCardRecords()->where('status', '=', UserRechargeableCard::STATUS_VALID)->first();
            $rechargeableCards = $this->scopeQuery(function (RechargeableCard $rechargeableCard) use ($cardType, $indefiniteCard) {
                return $rechargeableCard->active()->where(function (Builder $query) use ($cardType, $indefiniteCard) {
                    if ($cardType) {
                        $query->where('card_type', $cardType);
                    }
                    if ($indefiniteCard) {
                        $query->where('type', '<>', RechargeableCard::TYPE_INDEFINITE);
                    }
                });
            })->all();
        }

        return compact('rechargeableCards', 'balance');
    }
}
