<?php

\Route::get(config('messenger.uri_webhook'), ['uses' => 'WebHookController@getValidHook']);
\Route::post(config('messenger.uri_webhook'), ['uses' => 'WebHookController@postMessage']);
\Route::post(config('messenger.uri_webhook'), ['uses' => 'WebHookController@postMessage']);
