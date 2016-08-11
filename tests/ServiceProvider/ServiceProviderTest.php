<?php

use \Mockery;

class ServiceProviderTest extends MessengerTestCase
{

    public function testService()
    {
        $service = $this->app->getProvider('Distilleries\Messenger\MessengerServiceProvider');
        $facades = $service->provides();
        $this->assertTrue(['messenger'] == $facades);

        $service->boot($this->app->make('Illuminate\Routing\Router'));
        $service->register();
    }

}