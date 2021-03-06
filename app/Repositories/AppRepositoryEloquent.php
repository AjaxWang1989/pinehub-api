<?php

namespace App\Repositories;

use App\Repositories\Traits\Destruct;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\AppRepository;
use App\Entities\App;
use App\Validators\AppValidator;

/**
 * Class AppRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class AppRepositoryEloquent extends BaseRepository implements AppRepository
{
    use Destruct;
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return App::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
        App::creating(function (App &$app) {
            $app->id = uniqid('kdy');
            $app->secret = str_random(32);
        });
    }
    
}
