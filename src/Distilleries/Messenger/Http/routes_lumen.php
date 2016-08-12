<?php

$app->get(config('messenger.uri_webhook'), ['uses' => 'WebHookLumenController@getValidHook']);
$app->post(config('messenger.uri_webhook'), ['uses' => 'WebHookLumenController@postMessage']);