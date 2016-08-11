<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/08/2016
 * Time: 10:51
 */

use \Mockery as m;

abstract class MessengerTestCase extends \Orchestra\Testbench\TestCase
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

    public function assertHTTPExceptionStatus($expectedStatusCode, Closure $codeThatShouldThrow)
    {
        try
        {
            $codeThatShouldThrow($this);

            $this->assertFalse(true, "An HttpException should have been thrown by the provided Closure.");
        }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e)
        {
            // assertResponseStatus() won't work because the response object is null
            $this->assertEquals(
                $expectedStatusCode,
                $e->getStatusCode(),
                sprintf("Expected an HTTP status of %d but got %d.", $expectedStatusCode, $e->getStatusCode())
            );
        }
    }
}