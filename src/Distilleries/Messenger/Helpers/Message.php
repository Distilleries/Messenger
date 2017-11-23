<?php

namespace Distilleries\Messenger\Helpers;

/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 31/07/2016
 * Time: 19:50
 */

use Carbon\Carbon;
use Distilleries\Messenger\Exceptions\ConfigException;
use Distilleries\Messenger\Exceptions\MessengerException;
use Distilleries\Messenger\Models\MessengerLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Message
{

    protected $config = [];
    protected $client = null;

    /**
     * Message constructor.
     * @param array $config
     */
    public function __construct(array $config, Client $client)
    {
        $this->client = $client;

        if ($this->checkConfig($config) && !empty($client)) {
            $this->config = $config;
        } else {
            throw new ConfigException(trans('messenger::errors.config_not_valid'));
        }

    }

    protected function checkConfig(array $config)
    {
        return (empty($config['uri_bot']) || empty($config['page_access_token'])) ? false : true;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Client|null
     */
    public function getClient()
    {
        return $this->client;
    }


    public function sendTextMessage($recipientId, $messageText)
    {
        $messageData = [
            'recipient' => ['id' => $recipientId],
            'message'   => ['text' => $messageText]
        ];

        return $this->callSendAPI($messageData);
    }


    public function sendImageMessage($recipientId, $picture)
    {

        $messageData = [
            'recipient' => ['id' => $recipientId],
            'message'   => [
                'attachment' => [
                    'type'    => 'image',
                    'payload' =>
                        [
                            'url' => $picture
                        ]
                ]
            ]
        ];

        return $this->callSendAPI($messageData);
    }

    public function sendCard($recipientId, $card)
    {

        $messageData = [
            'recipient' => ['id' => $recipientId],
            'message'   => [
                'attachment' => [
                    'type'    => 'template',
                    'payload' => $card
                ]
            ]
        ];

        return $this->callSendAPI($messageData);
    }

    public function sendData($messageData, $recipientId) {
        if (property_exists($messageData, 'text') && is_array($messageData->text)) {
            $lastText = end($messageData->text);
            foreach ($messageData->text as $text) {
                if (intval($this->config['sleep_time_before_typing'])) {
                    usleep(intval($this->config['sleep_time_before_typing']) * 1000);
                }
                $this->typingOn($recipientId);
                if (intval($this->config['sleep_time_after_typing'])) {
                    usleep(intval($this->config['sleep_time_after_typing']) * 1000);
                }

                $multipleMessge = new \stdClass();
                if ($text == $lastText) {
                    $multipleMessge = clone $messageData;
                }
                $multipleMessge->text = $text;
                $this->callSendAPI([
                    "message"   => $multipleMessge,
                    "recipient" => [
                        "id" => $recipientId
                    ]
                ]);
            }
        } elseif (property_exists($messageData, 'attachment') && is_array($messageData->attachment)) {
            $lastItem = end($messageData->attachment);
            foreach ($messageData->attachment as $attachment) {
                if (intval($this->config['sleep_time_before_typing'])) {
                    usleep(intval($this->config['sleep_time_before_typing']) * 1000);
                }
                $this->typingOn($recipientId);
                if (intval($this->config['sleep_time_after_typing'])) {
                    usleep(intval($this->config['sleep_time_after_typing']) * 1000);
                }
                $multipleMessge = new \stdClass();
                if ($attachment == $lastItem) {
                    $multipleMessge = clone $messageData;
                    unset($multipleMessge->text);
                    unset($multipleMessge->attachment);
                }
                if (is_string($attachment)) { // Merge attachement with some text is possible
                    $multipleMessge->text = $attachment;
                    $this->callSendAPI([
                        "message"   => $multipleMessge,
                        "recipient" => [
                            "id" => $recipientId
                        ]
                    ]);
                } else {
                    $multipleMessge->attachment = $attachment;
                    $this->callSendAPI([
                        "message"   => $multipleMessge,
                        "recipient" => [
                            "id" => $recipientId
                        ]
                    ]);
                }
            }
        } else {
            $this->callSendAPI([
                "message"   => $messageData,
                "recipient" => [
                    "id" => $recipientId
                ]
            ]);
        }
        $this->typingOff($recipientId);
    }

    public function typingOn($recipientId) {
        return $this->callSendAPI([
            "recipient" => [
                "id" => $recipientId
            ],
            "sender_action" => "typing_on"
        ]);
    }

    public function typingOff($recipientId) {
        return $this->callSendAPI([
            "recipient" => [
                "id" => $recipientId
            ],
            "sender_action" => "typing_off"
        ]);
    }

    public function markSeen($recipientId) {
        return $this->callSendAPI([
            "recipient" => [
                "id" => $recipientId
            ],
            "sender_action" => "mark_seen"
        ]);
    }

    public function callSendAPI($messageData, $uri = 'uri_bot', $method = "POST")
    {
        try {
            $res = $this->client->request($method, $this->config[$uri], [
                'query' => ['access_token' => $this->config['page_access_token']],
                'json'  => $messageData
            ]);

            return $res->getBody()->getContents();

        } catch (ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getBody() && $e->getResponse()->getBody()) {
                MessengerLog::create([
                    'request' => json_encode($messageData),
                    'response' => $e->getResponse()->getBody()->getContents(),
                    'inserted_at' => Carbon::now()
                ]);
            }
            throw new MessengerException(trans('messenger::errors.unable_send_message'), 0, $e);
        }
    }

    public function getCurrentUserProfile($uid, $fields = null)
    {
        if (empty($fields)) {
            return (new FBUser($this->config, $this->getClient()))->getProfile($uid);
        }

        return (new FBUser($this->config, $this->getClient()))->getProfile($uid, $fields);

    }
}