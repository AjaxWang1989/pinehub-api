<?php

namespace App\Providers;

use App\Entities\Shop;
use Dingo\Api\Routing\Router;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        Broadcast::routes();
        $this->app->make('api.router')->version('v1', [], function (Router $router) {
            $router->any('/broadcasting/auth', ['as' => 'broadcasting.auth', 'uses' => BroadcastController::class.'@authenticate']);
        });
        Broadcast::channel('shop-{shopId}', function ($user, $shopId) {
            $shop = Shop::find($shopId);
            return $user->id === $shop->userId;
        });
    }
}
