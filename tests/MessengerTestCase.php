<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/08/2016
 * Time: 10:51
 */

use \Mockery as m;

abstract class MessengerTestCase extends \Orchestra\Testbench\BrowserKit\TestCase
{
    protected $facade;

    protected function initService()
    {
        $service       = $this->app->getProvider('Distilleries\Messenger\MessengerServiceProvider');
        $this->facades = $service->provides();
        $service->boot();
        $service->register();

        return $service;
    }

    public function setUp()
    {
        parent::setUp();
        $this->app['Illuminate\Contracts\Console\Kernel']->call('vendor:publish');
        $this->artisan('migrate');
        $this->refreshApplication();
    }



    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }


    protected function getPackageProviders($app)
    {
        return [
            'Distilleries\Messenger\MessengerServiceProvider',
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Messenger' => 'Distilleries\Messenger\Facades\Messenger',
        ];
    }


    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function assertHTTPExceptionStatus($expectedStatusCode, Closure $statusCodeReturned)
    {
        $code = $statusCodeReturned($this);
        $this->assertEquals(
            $expectedStatusCode,
            $statusCodeReturned($this),
            sprintf("Expected an HTTP status of %d but got %d.", $expectedStatusCode, $code)
        );
    }
}