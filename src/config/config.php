<?php

return [
    'uri_webhook'              => '/webhook',
    'validation_token'         => env('VALIDATION_TOKEN'),
    'page_access_token'        => env('PAGE_ACCESS_TOKEN'),
    'uri_open_graph'           => 'https://graph.facebook.com/v2.6/',
    'uri_bot'                  => 'https://graph.facebook.com/v2.6/me/messages',
    'uri_config'               => 'https://graph.facebook.com/v2.6/me/messenger_profile',
    'user_link_class'          => 'App\Models\User',
    'sleep_time_before_typing' => 200,
    'sleep_time_after_typing'  => 800,
    'user_link_field'          => 'email',
    /*
     * Log level:
     * error or info
     */
    'log_level'                => 'error',

];
