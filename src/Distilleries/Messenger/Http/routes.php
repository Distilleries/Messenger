<?php

\Route::get(config('messenger.uri_webhook'), ['uses' => 'WebHookController@getValidHook']);
\Route::post(config('messenger.uri_webhook'), ['uses' => 'WebHookController@postMessage']);
\Route::post(config('messenger.uri_webhook'), ['uses' => 'WebHookController@postMessage']);

\Route::group(['prefix' => config('expendable.admin_base_uri'), 'middleware' => ['auth', 'permission', 'language']], function () {
    \Route::controller('messenger-user', 'Backend\MessengerUserController');
    \Route::controller('messenger-logs', 'Backend\MessengerLogsController');
});