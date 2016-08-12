<?php

use GuzzleHttp\Psr7;

class FBUserTest extends MessengerTestCase
{

    public function testHelperConstructorException()
    {
        try {
            new \Distilleries\Messenger\Helpers\FBUser([], new \GuzzleHttp\Client());
            $this->assertFalse(true, "An HttpException should have been thrown by the provided Closure.");
        } catch (\Distilleries\Messenger\Exceptions\ConfigException $e) {
            $this->assertEquals($e->getMessage(), trans('messenger::errors.config_not_valid'));
        }
    }

    public function testHelperConstructor()
    {
        try {
            $config = [
                'uri_open_graph'    => '/graph',
                'page_access_token' => 'Token',
            ];

            $helper = new \Distilleries\Messenger\Helpers\FBUser($config, new \GuzzleHttp\Client());
            $this->assertEquals($helper->getConfig(), $config);

        } catch (\Distilleries\Messenger\Exceptions\ConfigException $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }

    public function testGetProfileException()
    {
        try {
            $config = [
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
                'page_access_token' => 'Token',
            ];

            (new \Distilleries\Messenger\Helpers\FBUser($config, new \GuzzleHttp\Client()))->getProfile('1');

            $this->assertFalse(true, "An HttpException should have been thrown by the provided Closure.");

        } catch (\Distilleries\Messenger\Exceptions\MessengerException $e) {
            $this->assertEquals($e->getMessage(), trans('messenger::errors.unable_to_load_user_profile'));
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
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
                'page_access_token' => 'Token',
            ];

            $profile = (new \Distilleries\Messenger\Helpers\FBUser($config, $client))->getProfile('1');
            $this->assertEquals($profile, json_decode(json_encode($datas), false));

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }

    public function testGetClient()
    {

        $client = new \GuzzleHttp\Client();

        try {
            $config = [
                'uri_open_graph'    => 'https://graph.facebook.com/v2.6/',
                'page_access_token' => 'Token',
            ];

            $fbUser = (new \Distilleries\Messenger\Helpers\FBUser($config, $client));
            $this->assertEquals($fbUser->getClient(), $client);

        } catch (Exception $e) {
            $this->assertFalse(true, "An HttpException should not have been thrown by the provided Closure.");
        }
    }
}

