<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\Ticket
 *
 * @property int $id
 * @property string $cardId 卡券id
 * @property string|null $wechatAppId 微信app id
 * @property string|null $aliAppId 支付宝app id
 * @property string|null $appId 系统app id
 * @property string $cardType 卡券类型
 * @property array $cardInfo 卡券信息
 * @property int $status 0-审核中 1-审核通过 2-审核未通过
 * @property int $sync -1 不需要同步 0 - 同步失败 1-同步中 2-同步成功
 * @property \Illuminate\Support\Carbon|null $beginAt 开始日期
 * @property \Illuminate\Support\Carbon|null $endAt 结束时间
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property string|null $deletedAt
 * @property-read \App\Entities\App|null $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Entities\CustomerTicketCard[] $records
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereAliAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereBeginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereCardInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereSync($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Ticket whereWechatAppId($value)
 * @mixin \Eloquent
 */
class Ticket extends Card
{
    protected $table = 'cards';

    const UNAVAILABLE = 3;//unavailable

    public function orders() : HasMany
    {
        return $this->hasMany(Order::class, 'card_id', 'id');
    }

    public function customerTickets() : HasMany
    {
        return $this->hasMany(CustomerTicketCard::class, 'card_id', 'id');
    }
}
