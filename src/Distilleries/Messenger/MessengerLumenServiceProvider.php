<?php namespace Distilleries\Messenger;

use Distilleries\Messenger\Helpers\Message;
use Distilleries\Messenger\Helpers\Messenger;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageServiceProviderLumen;

class MessengerLumenServiceProvider extends ServiceProvider {


    protected $package = 'messenger';
    protected $namespace = 'Distilleries\Messenger\Http\Controllers';


    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../views', $this->package);
        $this->loadTranslationsFrom(__DIR__.'/../../lang', $this->package);
        $this->map();
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

        $this->app->singleton('messenger', function($app) {
            return new Message($app['config']->get('messenger'),new Client());
        });

        $this->app->register(ImageServiceProviderLumen::class);

        $this->app->singleton('Distilleries\Messenger\Contracts\MessengerReceiverContract', function ($app) {
            return new Messenger();
        });

        $this->alias();
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

        $this->app->alias('Route','Illuminate\Support\Facades\Route');
        $this->app->alias('Log','Illuminate\Support\Facades\Log');

    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->app->group(['namespace' => $this->namespace], function($app)
        {
            require __DIR__ . '/Http/routes_lumen.php';
        });
    }
}