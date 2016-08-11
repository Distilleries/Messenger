<?php

class ComponentControllerTest extends MessengerTestCase
{


    public function setUp()
    {
        parent::setUp();

        $this->app['Distilleries\Messenger\Contracts\MessengerReceiverContract'] = $this->app->share(function ($app) {
            $mock = Mockery::mock('Distilleries\Messenger\Contracts\MessengerReceiverContract')
                ->shouldReceive(['receivedAuthentication', 'receivedMessage', 'receivedDeliveryConfirmation', 'receivedPostback', 'defaultHookUndefinedAction'])
                ->getMock();

            $mock->shouldReceive('receivedAuthentication')
                ->andReturn('receivedAuthentication');

            $mock->shouldReceive('receivedMessage')
                ->andReturn('receivedMessage');

            $mock->shouldReceive('receivedDeliveryConfirmation')
                ->andReturn('receivedDeliveryConfirmation');

            $mock->shouldReceive('receivedPostback')
                ->andReturn('receivedPostback');

            $mock->shouldReceive('defaultHookUndefinedAction')
                ->andReturn('defaultHookUndefinedAction');


            return $mock;
        });
    }


    public function testGetValidHookError()
    {

        $this->assertHTTPExceptionStatus(403, function ($this) {
            $this->call('GET', config('messenger.uri_webhook'));
        });
    }

    public function testGetValidHookInvalidParamHubVerifyToken()
    {

        $this->assertHTTPExceptionStatus(403, function ($this) {
            $this->call('GET', config('messenger.uri_webhook'), ['hub_mode' => 'subscribe', 'hub_verify_token' => str_random(20)]);
        });
    }


    public function testGetValidHookInvalidParamHub()
    {
        $token = str_random(40);
        $this->app['config']->set('messenger.validation_token', $token);
        $this->assertHTTPExceptionStatus(403, function ($this) use ($token) {
            $this->call('GET', config('messenger.uri_webhook'), ['hub_mode' => 'test', 'hub_verify_token' => $token]);
        });
    }

    public function testGetValidHook()
    {
        $token = str_random(40);
        $this->app['config']->set('messenger.validation_token', $token);
        $response = $this->call('GET', config('messenger.uri_webhook'), [
            'hub_mode'         => 'subscribe',
            'hub_verify_token' => $token,
            'hub_challenge'    => "That work",
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('That work', $response->getContent());
    }


    public function testPostMessageError()
    {
        $this->assertHTTPExceptionStatus(403, function ($this) {
            $this->call('POST', config('messenger.uri_webhook'));
        });
    }


    public function testPostMessageNoAction()
    {

        $response = $this->call('POST', config('messenger.uri_webhook'), [
            'object' => 'page',
            'entry'  => [
                [
                    'messaging' => [
                        'test'
                    ]
                ]
            ],
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('defaultHookUndefinedAction', $response->getContent());

    }


    public function testPostMessageReceivedAuthentication()
    {

        $response = $this->call('POST', config('messenger.uri_webhook'), [
            'object' => 'page',
            'entry'  => [
                [
                    'messaging' => [
                        ['optin' => true]
                    ]
                ]
            ],
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('receivedAuthentication', $response->getContent());

    }


    public function testPostMessageReceivedMessage()
    {

        $response = $this->call('POST', config('messenger.uri_webhook'), [
            'object' => 'page',
            'entry'  => [
                [
                    'messaging' => [
                        ['message' => 'Hi!']
                    ]
                ]
            ],
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('receivedMessage', $response->getContent());

    }


    public function testPostMessageReceivedDeliveryConfirmation()
    {

        $response = $this->call('POST', config('messenger.uri_webhook'), [
            'object' => 'page',
            'entry'  => [
                [
                    'messaging' => [
                        ['delivery' => true]
                    ]
                ]
            ],
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('receivedDeliveryConfirmation', $response->getContent());

    }

    public function testPostMessageReceivedPostback()
    {

        $response = $this->call('POST', config('messenger.uri_webhook'), [
            'object' => 'page',
            'entry'  => [
                [
                    'messaging' => [
                        ['postback' => true]
                    ]
                ]
            ],
        ]);

        $this->assertEquals(200, $response->status());
        $this->assertContains('receivedPostback', $response->getContent());

    }
}

