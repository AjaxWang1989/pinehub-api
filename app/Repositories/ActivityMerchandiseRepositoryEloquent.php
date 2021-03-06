<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\ActivityMerchandiseRepository;
use App\Entities\ActivityMerchandise;
use App\Validators\ActivityMerchandiseValidator;

/**
 * Class ActivityMerchandiseRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class ActivityMerchandiseRepositoryEloquent extends BaseRepository implements ActivityMerchandiseRepository
{
    protected $fieldSearchable = [
        'merchandise.name' => 'like',
        'sell_num'=>'*',
        'sell_price' => '*',
        'merchandise.status' => '='
    ];
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ActivityMerchandise::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * @param int $activityId
     * @param string $limit
     */
    public function newActivityMerchandises(int $activityId)
    {
        $this->scopeQuery(function (ActivityMerchandise $activityMerchandise) use ($activityId){
            return $activityMerchandise
                ->with('merchandise')
                ->where('activity_id',$activityId);
        });
        return $this->paginate();
    }
}
