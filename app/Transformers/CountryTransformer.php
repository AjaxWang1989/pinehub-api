<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entities\Country;

/**
 * Class CountryTransformer.
 *
 * @package namespace App\Transformers;
 */
class CountryTransformer extends TransformerAbstract
{
    /**
     * Transform the Country entity.
     *
     * @param \App\Entities\Country $model
     *
     * @return array
     */
    public function transform(Country $model)
    {
        return [
            'id'         => (int) $model->id,
            'name'       => $model->name,
            'code'       => $model->code,
            /* place your other model properties here */

            'created_at' => $model->createdAt,
            'updated_at' => $model->updatedAt
        ];
    }
}
