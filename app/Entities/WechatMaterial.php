<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;
use App\Entities\Traits\ModelAttributesAccess;

/**
 * Class WechatMaterial.
 *
 * @property int $id
 * @property string|null $title 素材名称
 * @property string|null $introduction 素材介绍
 * @property int         $isTmp 是否临时素材
 * @property string|null $mediaId 素材id
 * @property string|null $url 图片url
 * @property string|null $type 图片
 * @property array       $articles 图文
 * @property \Carbon\Carbon $expiresAt 临时素材过期日期
 * @property \Carbon\Carbon|null $createdAt
 * @property \Carbon\Carbon|null $updatedAt
 * @package namespace App\Entities;
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereArticles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereIsTmp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\WechatMaterial whereUrl($value)
 */
class WechatMaterial extends Model implements Transformable
{
    use TransformableTrait,ModelAttributesAccess;

    protected $casts = [
        'articles' => 'json',
        'expires_at' => 'date',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','introduction','is_tmp','media_id','url','type','articles','expires_at'
    ];

}
