<?php

use GuzzleHttp\Psr7;

class MessageTest extends MessengerTestCase
{

    public function testHelperConstructorException()
    {
        try {
            new \Distilleries\Messenger\Helpers\Message([], new \GuzzleHttp\Client());
            $this->assertFalse(true, "An HttpException should have been thrown by the provided Closure.");
        } catch (\Distilleries\Messenger\Exceptions\ConfigException $e) {
            $this->assertEquals($e->getMessage(), trans('messenger::errors.config_not_valid'));
        }
    }

    public function testHelperConstructor()
    {
        try {
            $config = [
                'uri_bot'           => '/graph',
                'page_access_token' => 'Token',
            ];

            $helper = new \Distilleries\Messenger\Helpers\Message($config, new \GuzzleHttp\Client());
            $this->assertEquals($helper->getConfig(), $config);

        } catch (\Distilleries\Messenger\Exceptions\ConfigException $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }

    public function testHelperFacade()
    {

        $this->app['config']->set('messenger.validation_token', 'token');
        $this->app['config']->set('messenger.page_access_token', 'token');
        $this->assertTrue(is_a($this->app['messenger'], \Distilleries\Messenger\Helpers\Message::class));
    }


    public function testGetClient()
    {

        $client = new \GuzzleHttp\Client();

        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => '/graph',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $message = (new \Distilleries\Messenger\Helpers\Message($config, $client));
            $this->assertEquals($message->getClient(), $client);

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }


    public function testGetProfile()
    {

        $datas = ["first_name" => "Max", "last_name" => "Max", "profile_pic" => "/test.jpg", "locale" => "", "timezone" => "", "gender" => ""];

        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Psr7\Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for(json_encode($datas))),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $client  = new \GuzzleHttp\Client(['handler' => $handler]);


        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => '/graph',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $profile = (new \Distilleries\Messenger\Helpers\Message($config, $client))->getCurrentUserProfile('1');
            $this->assertEquals($profile, json_decode(json_encode($datas), false));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }

    public function testGetProfileCustomFields()
    {

        $datas = ["first_name" => "Max", "last_name" => "Max", "profile_pic" => "/test.jpg"];

        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Psr7\Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for(json_encode($datas))),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $client  = new \GuzzleHttp\Client(['handler' => $handler]);


        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => '/graph',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $profile = (new \Distilleries\Messenger\Helpers\Message($config, $client))->getCurrentUserProfile('1',['first_name','last_name','profile_pic']);
            $this->assertEquals($profile, json_decode(json_encode($datas), false));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }


    public function testSendTextMessage(){

        $datas = ["result" => "Ok"];
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Psr7\Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for(json_encode($datas))),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $client  = new \GuzzleHttp\Client(['handler' => $handler]);

        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => 'https://graph.facebook.com/v2.6/',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $message = (new \Distilleries\Messenger\Helpers\Message($config, $client))->sendTextMessage(1,'Test');
            $this->assertEquals($message, json_encode($datas));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }

    }

    public function testSendImageMessage(){

        $datas = ["result" => "Ok"];
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Psr7\Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for(json_encode($datas))),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $client  = new \GuzzleHttp\Client(['handler' => $handler]);

        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => 'https://graph.facebook.com/v2.6/',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $message = (new \Distilleries\Messenger\Helpers\Message($config, $client))->sendImageMessage(1,'Test');
            $this->assertEquals($message, json_encode($datas));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }

    }
    public function testSendCard(){

        $datas = ["result" => "Ok"];
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new Psr7\Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for(json_encode($datas))),
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mock);
        $client  = new \GuzzleHttp\Client(['handler' => $handler]);

        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => 'https://graph.facebook.com/v2.6/',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            $message = (new \Distilleries\Messenger\Helpers\Message($config, $client))->sendCard(1,[
                'template_type' => 'generic',
                'elements'      => [
                    [
                        "title"     => "Messenger Boilerplate",
                        "image_url" => env('APP_URL') . '/assets/images/logo.png',
                        "subtitle"  => "example subtitle",
                        'buttons'   => [
                            [
                                'type'  => "web_url",
                                'url'   => "https://github.com/Distilleries/lumen-messenger-boilerplate",
                                'title' => "Come download it!"
                            ]
                        ]
                    ]

                ]
            ]);
            $this->assertEquals($message, json_encode($datas));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }

    }


    public function testClientException(){


        try {
            $config = [
                'page_access_token' => 'Token',
                'uri_bot'           => 'https://graph.facebook.com/v2.6/',
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
            ];

            (new \Distilleries\Messenger\Helpers\Message($config, new \GuzzleHttp\Client()))->sendTextMessage(1,'Test');
            $this->assertFalse(true, "An HttpException should have been thrown by the provided Closure.");

        } catch (\Distilleries\Messenger\Exceptions\MessengerException $e) {
            $this->assertEquals($e->getMessage(), trans('messenger::errors.unable_send_message'));
        }

    }
}

