<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/13
 * Time: 下午12:40
 */
namespace App\Routes;
use Dingo\Api\Routing\Router as DingoRouter;
use Dingo\Api\Routing\Router;
use Laravel\Lumen\Routing\Router as LumenRouter;
class WebApiRoutes extends ApiRoutes
{

    /**
     * @param DingoRouter|LumenRouter $router
     * */
    protected function subRoutes($router)
    {
        tap($router, function (Router $router) {
            parent::subRoutes($router); // TODO: Change the autogenerated stub

            $router->group(['middleware' => ['api.auth']], function ($router) {
                /**
                 * @var  LumenRouter|DingoRouter $router
                 * */
                $router->get('/users',['as' => 'users.list', 'uses' => 'UsersController@getUsers']);
                $router->get('/user/{id}',['as' => 'user.detail', 'uses' => 'UsersController@getUserDetail']);
                //登录用户信息路由
                $router->get('/self/info', ['as' => 'self.info', 'uses' => 'MySelfController@selfInfo']);
                $router->put('/change/password', ['as' => 'change.password', 'uses' => 'MySelfController@changePassword']);

                $router->post('/shop', ['as' => 'shop.create', 'uses' => 'ShopsController@store']);
                $router->get('/shops', ['as' => 'shop.list', 'uses' => 'ShopsController@getShops']);
                $router->get('/shop/{id}', ['as' => 'shop.detail', 'uses' => 'ShopsController@getShopDetail']);
                $router->put('/shop/{id}', ['as' => 'shop.update', 'uses' => 'ShopsController@update']);
                $router->post('/app/logo/{driver?}', ['as' => 'app.logo.upload', 'uses' => 'AppController@uploadLogo']);
                $router->get('/apps', ['as' => 'app.list', 'uses' => 'AppController@index']);
                $router->post('/app', ['as' => 'app.create', 'uses' => 'AppController@store']);
                $router->put('/app/{id}', ['as' => 'app.update', 'uses' => 'AppController@update']);
                $router->get('/app/{id}', ['as' => 'app.show', 'uses' => 'AppController@show']);
                $router->delete('/app/{id}', ['as' => 'app.delete', 'uses' => 'AppController@destroy']);
            });

            $router->group(["prefix" => "wechat", "namespace" => "Wechat"], function ($router) {
                /**
                 * @var LumenRouter|DingoRouter $router
                 * */
                $router->post("config", ['as' => 'wechat.config.create', 'uses' => 'ConfigController@store']);
                $router->get("configs", ['as' => 'wechat.config.list', 'uses' => 'ConfigController@index']);
                $router->get("config/{id}", ['as' => 'wechat.config.show', 'uses' => 'ConfigController@show']);
                $router->put("config/{id}", ['as' => 'wechat.config.update', 'uses' => 'ConfigController@update']);
                $router->delete("configs", ['as' => 'wechat.config.delete.bat', 'uses' => 'ConfigController@destroy']);
                $router->delete("config/{id}", ['as' => 'wechat.config.delete', 'uses' => 'ConfigController@destroy']);

                //menus
                $router->post("menu", ['as' => 'wechat.menu.create', 'uses' => 'MenuController@store']);
                $router->get("menus", ['as' => 'wechat.menu.list', 'uses' => 'MenuController@index']);
                $router->get("{appId}/menus", ['as' => 'wechat.app.menus', 'uses' => 'MenuController@index']);
                $router->get("menu/{id}", ['as' => 'wechat.menu.show', 'uses' => 'MenuController@show']);
                $router->put("menu/{id}", ['as' => 'wechat.menu.update', 'uses' => 'MenuController@update']);
                $router->delete("menu/{id}", ['as' => 'wechat.menu.delete', 'uses' => 'MenuController@destroy']);
                $router->delete("menus", ['as' => 'wechat.menu.delete.bat', 'uses' => 'MenuController@destroy']);
                $router->get("menu/{id}/sync", ['as' => 'wechat.menu.sync', 'uses' => 'MenuController@sync']);

                //material api

                $router->post("media/temporary", ['as' => 'wechat.temporary.media.create', 'uses' => 'MaterialController@storeTemporaryMedia']);
                $router->post("material/article", ['as' => 'wechat.article.create', 'uses' => 'MaterialController@storeForeverNews']);
                $router->post("{type}/material", ['as' => 'wechat.material.create', 'uses' => 'MaterialController@uploadForeverMaterial']);
                $router->get("material/stats", ['as' => 'wechat.material.stats', 'uses' => 'MaterialController@materialStats']);
                $router->get("materials", ['as' => 'wechat.materials', 'uses' => 'MaterialController@materialList']);
                $router->get("material", ['as' => 'wechat.material.view', 'uses' => 'MaterialController@materialView']);
                $router->get("material/{mediaId}", ['as' => 'wechat.material.forever.detail', 'uses' => 'MaterialController@material']);
                $router->get("material/{mediaId}/{type}", ['as' => 'wechat.material.temporary.detail', 'uses' => 'MaterialController@material']);
                $router->put("material/article/{mediaId}", ['as' => 'wechat.article.update', 'uses' => 'MaterialController@materialNewsUpdate']);
                $router->delete("material/{mediaId}", ['as' => 'wechat.material.delete', 'uses' => 'MenuController@deleteMaterial']);

                //auto reply message
                $router->post("auto_reply_message", ['as' => 'wechat.auto_reply_message.create', 'uses' => 'AutoReplyMessagesController@store']);
                $router->get("auto_reply_messages", ['as' => 'wechat.auto_reply_message.list', 'uses' => 'AutoReplyMessagesController@index']);
                $router->get("auto_reply_message/{id}", ['as' => 'wechat.auto_reply_message.show', 'uses' => 'AutoReplyMessagesController@show']);
                $router->put("auto_reply_message/{id}", ['as' => 'wechat.auto_reply_message.update', 'uses' => 'AutoReplyMessagesController@update']);
                $router->delete("auto_reply_message/{id}", ['as' => 'wechat.auto_reply_message.delete', 'uses' => 'AutoReplyMessagesController@destroy']);
                $router->delete("auto_reply_messages", ['as' => 'wechat.auto_reply_message.delete.bat', 'uses' => 'AutoReplyMessagesController@destroy']);
            });
            $router->get('/countries', ['as' => 'country.list', 'uses' => 'CountryController@getCountries']);
            $router->get('/country/{id}', ['as' => 'country.detail', 'uses' => 'CountryController@getCountryDetail']);
            $router->get('/country/{countryId}/provinces', ['as' => 'province.list', 'uses' => 'ProvinceController@getProvinces']);
            $router->get('/province/{id}', ['as' => 'province.detail', 'uses' => 'ProvinceController@getProvinceDetail']);
            $router->get('/country/{countryId}/province/{provinceId}/cities', ['as' => 'city.list', 'uses' => 'CityController@getCities']);
            $router->get('/city/{id}', ['as' => 'city.detail', 'uses' => 'CityController@getCityDetail']);
            $router->get('/country/{countryId}/province/{provinceId}/city/{cityId}/counties', ['as' => 'county.list', 'uses' => 'CountyController@getCounties']);
            $router->get('/county/{id}', ['as' => 'county.detail', 'uses' => 'CountyController@getCountyDetail']);
        });
    }
}