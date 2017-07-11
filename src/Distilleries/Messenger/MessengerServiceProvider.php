<?php namespace Distilleries\Messenger;

use Distilleries\Messenger\Console\LoadMessengerJson;
use Distilleries\Messenger\Helpers\Message;
use Distilleries\Messenger\Helpers\Messenger;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Intervention\Image\ImageServiceProvider;

class MessengerServiceProvider extends ServiceProvider {


    protected $package = 'messenger';
    protected $namespace = 'Distilleries\Messenger\Http\Controllers';


    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', $this->package);
        $this->loadTranslationsFrom(__DIR__.'/../../lang', $this->package);
        $this->publishes([
            __DIR__.'/../../config/config.php'    => config_path($this->package.'.php'),
            __DIR__ . '/../../database/migrations/' => base_path('/database/migrations'),
        ]);

        if (! $this->app->routesAreCached()) {
            $this->map($this->app->make('Illuminate\Routing\Router'));
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            $this->package
        );


        $this->app->register(ImageServiceProvider::class);

        $this->app->singleton('messenger', function($app) {
            return new Message($app['config']->get('messenger'),new Client());
        });

        $this->app->singleton('Distilleries\Messenger\Contracts\MessengerReceiverContract', function ($app) {
            return new Messenger();
        });

        $this->alias();
        $this->commands([
            LoadMessengerJson::class
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return array('messenger');
    }


    public function alias() {

        AliasLoader::getInstance()->alias(
            'Route',
            'Illuminate\Support\Facades\Route'
        );
        AliasLoader::getInstance()->alias(
            'Log',
            'Illuminate\Support\Facades\Log'
        );
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function()
        {
            require __DIR__ . '/Http/routes.php';
        });
    }
}