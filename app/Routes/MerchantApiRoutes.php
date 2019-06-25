<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/28
 * Time: 上午9:27
 */

namespace App\Routes;


use Dingo\Api\Routing\Router as DingoRouter;
use Laravel\Lumen\Routing\Router as LumenRouter;

class MerchantApiRoutes extends ApiRoutes
{
    /**
     * @param LumenRouter|DingoRouter $router
     * */
    public function subRoutes($router)
    {
        parent::subRoutes($router); // TODO: Change the autogenerated stub
        $router->get('/login', ['as' => 'merchant.login','uses' => 'AuthController@login']);
        $router->get('/app/access', ['as' => 'merchant.app.access','uses' => 'AuthController@appAccess']);
        $router->get('/verify/code/sms/{mobile}', ['as' => 'merchant.verify.code','uses' => 'AuthController@verifyCode']);

        $attributes = [];

        if($this->app->environment() !== 'local') {
            $attributes['middleware'] = ['api.auth'];
        }

        $router->group($attributes, function ($router) {

            /** @var DingoRouter $router */
            $router->get('/store/orders', ['as' =>'store.orders', 'uses' => 'OrderController@orders']);
            $router->get('/store/{id}/order/notice', ['as' =>'order.notice', 'uses' => 'NoticeController@notice']);
            $router->post('/register/getui', ['as' =>'order.notice', 'uses' => 'NoticeController@registerGetTuiClientId']);
        });
    }
}