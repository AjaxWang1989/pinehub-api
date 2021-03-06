<?php

namespace App\Transformers;

use App\Entities\Category;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;
use App\Entities\Merchandise;

/**
 * Class MerchandiseTransformer.
 *
 * @package namespace App\Transformers;
 */
class MerchandiseTransformer extends TransformerAbstract
{
    /**
     * Transform the Merchandise entity.
     *
     * @param \App\Entities\Merchandise $model
     *
     * @return array
     */
    public function transform(Merchandise $model)
    {
        return [
            'id'         => (int) $model->id,
            'categories' => $model->categories->map(function (Category $category) {
                return $category->id;
            })->toArray(),
            /* place your other model properties here */
            'app_id' => $model->appId,
            'code' => $model->code,
            'name' => $model->name,
            'main_image' => $model->mainImage,
            'tags' => $model->tags,
            'images' => $model->images,
            'preview' => $model->preview,
            'detail' => $model->detail,
            'origin_price' => number_format($model->originPrice ? $model->originPrice : 0, 2),
            'sell_price' => number_format($model->sellPrice ? $model->sellPrice : 0, 2),
            'cost_price' => $model->costPrice ? number_format($model->costPrice, 2) : null,
            'factory_price' => $model->factoryPrice ? number_format($model->factoryPrice, 2) : null,
            'stock_num' => $model->stockNum,
            'status' => $model->status,
            'sell_num' => $model->sellNum,
            'created_at' => $model->createdAt,
            'updated_at' => $model->updatedAt
        ];
    }
}
