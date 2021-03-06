<?php
/**
 * Created by PhpStorm.
 * User: wang
 * Date: 2018/4/13
 * Time: 下午12:48
 */

namespace App\Routes;


use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Router as DingoRouter;
use Illuminate\Routing\Router;
use Laravel\Lumen\Routing\Router as LumenRouter;
use Laravel\Lumen\Application;

class Routes
{
    protected $namespace = 'App\Http\Controllers';

    protected $subNamespace = null;

    protected $prefix = '';

    protected $domain = '';

    protected $version = null;


    /**
     *@var Application $app
     * */
    protected $app = null;

    /**
     * @var LumenRouter|DingoRouter|Router $router
     * */
    protected $router = null;

    protected $auth = null;

    public function __construct(Application $app, $version = null, $namespace = null, $prefix =null, $domain = null, string  $auth = null)
    {
        $this->app = $app;
        $this->auth = $auth;
        if($namespace && $namespace[0] !== '\\') {
            $namespace = '\\'.$namespace;
        }
        $this->subNamespace = $namespace;
        $this->prefix = $prefix;
        $this->domain = $domain;
        $this->version = $version;
        if($this->app->runningInConsole()){
            $this->router = $app->make('router');
        }
    }

    public function load($version = null)
    {
        $this->boot();
        $this->app->make('router')->group([
            //'namespace' => 'App\Http\Controllers',
            //'middleware' => 'cross'
        ], function () use($version){
            $this->routesRegister($version);
        });
    }

    protected function routesRegister($version = null)
    {

    }

    protected function boot()
    {

    }



    /**
     * @param DingoRouter|LumenRouter $router
     * */
    protected function subRoutes($router)
    {

    }

    public function domain()
    {
        return $this->domain;
    }

    public function prefix()
    {
        return $this->prefix;
    }

    public function router()
    {
        return $this->router;
    }
}