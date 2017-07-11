<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 01/08/2016
 * Time: 16:30
 */

namespace Distilleries\Messenger\Helpers;

use Carbon\Carbon;
use Distilleries\Messenger\Contracts\MessengerReceiverContract;
use Distilleries\Messenger\Models\MessengerUser;
use Log;

class Messenger implements MessengerReceiverContract
{

    public $messenger = null;

    /**
     * Messenger constructor.
     * @param null $messenger
     */
    public function __construct()
    {
        $this->messenger = app('messenger');
    }


    public function defaultHookUndefinedAction($event)
    {
        return null;
    }

    /*
     * Authorization Event
     *
     * The value for 'optin.ref' is defined in the entry point. For the "Send to
     * Messenger" plugin, it is the 'data-ref' field. Read more at
     * https://developers.facebook.com/docs/messenger-platform/webhook-reference/authentication
     *
     */
    public function receivedAuthentication($event)
    {
        $senderID = $event->sender->id;
        $this->messenger->sendTextMessage($senderID, "Authentication successful");
    }


    public function receivedMessage($event)
    {

        Log::info(\GuzzleHttp\json_encode($event));

        $message            = $event->message;
        $messageText        = !empty($message->text) ? $message->text : null;
        $messageAttachments = !empty($message->attachments) ? $message->attachments : null;

        $senderID  = $event->sender->id;
        $messengerUser = $this->getMessengerUser($senderID);

        if ($messageText) {

            $this->doActionFromGrammar($messageText, $event);

        } elseif ($messageAttachments) {
            $this->doActionFromAttachment($messageAttachments, $event);

        }

    }

    protected function getMessengerUser($senderId) {
        $user = MessengerUser::where('sender_id', $senderId)->first();
        if (!$user) {
            $profile = $this->messenger->getCurrentUserProfile($senderId);
            $user = MessengerUser::create(['sender_id' => $senderId, 'email' => $profile->email]);
        }
        $user->update([
            'last_conversation_date' => Carbon::now()
        ]);
    }


    /*
     * Delivery Confirmation Event
     *
     * This event is sent to confirm the delivery of a message. Read more about 
     * these fields at https://developers.facebook.com/docs/messenger-platform/webhook-reference/message-delivered
     *
     */
    public function receivedDeliveryConfirmation($event)
    {
        $delivery   = $event->delivery;
        $messageIDs = $delivery->mids;

        if ($messageIDs) {
            foreach ($messageIDs as $messageID) {
                Log::info("Received delivery confirmation for message ID: " . $messageID);

            }
        }
    }


    public function receivedPostback($event)
    {
        $senderID = $event->sender->id;
        $payload = $event->postback->payload;
        $this->messenger->sendTextMessage($senderID, "Postback called".$payload);
    }


    /*---------------------------------------------------------------------------------------------------------------------------------------------*/
    /*---------------------------------------------------------------------------------------------------------------------------------------------*/
    /*---------------------------------------------------------------------------------------------------------------------------------------------*/

    protected function doActionFromGrammar($messageText, $event)
    {

        $senderID  = $event->sender->id;
        $messageId = $event->message->mid;

        if (GrammarAnalyser::saySocial($messageText)) {
            $this->quickReply($senderID);
        } else {
            if (GrammarAnalyser::sayHello($messageText)) {
                $this->sayHello($senderID, $messageId);
            } else {
                if (GrammarAnalyser::logoAsking($messageText)) {
                    $this->sendBBSLogo($senderID);
                } else {
                    if (GrammarAnalyser::whoIAmAsking($messageText)) {
                        $this->sendButtonWoAreWeMessage($event);
                    } else {
                        $this->repeatMessage($senderID, $messageText);
                    }
                }
            }
        }
    }

    protected function doActionFromAttachment($messageAttachments, $event)
    {

        $senderID  = $event->sender->id;
        $messageId = $event->message->mid;


        foreach ($messageAttachments as $attachment) {
            switch ($attachment->type) {
                case 'image':
                    $this->messenger->sendImageMessage($senderID, $attachment->payload->url);
                    break;
                default:
                    $this->messenger->sendTextMessage($senderID, "Message with attachment received");
            }
        }
    }

    protected function repeatMessage($senderID, $messageText)
    {

        if (strlen($messageText) == 4) {
            $urlGif = "";
            $code   = intval(unpack('V', iconv('UTF-8', 'UCS-4LE', $messageText))[1]);

            if ($code == 128008) {
                $urlGif = "https://media.giphy.com/media/8JIRQqil8mvEA/giphy.gif";
            }
            if ($code == 128007) {
                $urlGif = "https://media.giphy.com/media/g2n5wagSFzBMk/giphy.gif";
            }

            if ($urlGif == "") {
                $this->messenger->sendTextMessage($senderID, $code);
            } else {
                $this->messenger->sendImageMessage($senderID, $urlGif);
            }


        } else {
            $this->messenger->sendTextMessage($senderID, $messageText);
        }


    }

    protected function quickReply($senderID)
    {

        Log::info("Quick reply");
        $messageData = [
            'recipient' => ['id' => $senderID],
            'message'   => [
                'text'          => 'Réseau social favorie :',
                "quick_replies" => [
                    [
                        "content_type" => "text",
                        "title"        => "",
                        "payload"      => "TWITTER",
                        "image_url"    => env('APP_URL') . '/assets/images/twitter.png'
                    ],
                    [
                        "content_type" => "text",
                        "title"        => "",
                        "payload"      => "FACEBOOK",
                        "image_url"    => env('APP_URL') . '/assets/images/facebook.png'
                    ],
                    [
                        "content_type" => "text",
                        "title"        => "",
                        "payload"      => "SNAPCHAT",
                        "image_url"    => env('APP_URL') . '/assets/images/snapchat.png'
                    ]
                ]
            ]
        ];

        return $this->messenger->callSendAPI($messageData);
    }

    protected function sendBBSLogo($senderID)
    {
        $this->messenger->sendImageMessage($senderID, env('APP_URL') . '/assets/images/logo.png');
    }

    protected function sayHello($senderID, $messageId)
    {
        try {
            $profile = $this->messenger->getCurrentUserProfile($senderID);
            $this->messenger->sendTextMessage($senderID, "Bonjour " . $profile->first_name . ' ' . $profile->last_name);
        } catch (\Exception $e){
            $this->messenger->sendTextMessage($senderID, "Bonjour !");
        }

    }

    protected function sendButtonWoAreWeMessage($event)
    {
        $senderID = $event->sender->id;

        $this->messenger->sendCard($senderID, [
            'template_type' => 'generic',
            'elements'      => [
                [
                    "title"     => "Big Boss Studio",
                    "image_url" => env('APP_URL') . '/assets/images/logo.png',
                    "subtitle"  => "something BIG is coming !",
                    'buttons'   => [
                        [
                            'type'  => "web_url",
                            'url'   => "http://big-boss.com",
                            'title' => "Viens sur mon site!"
                        ],
                        [
                            'type'    => "phone_number",
                            'title'   => "Appelle moi!",
                            'payload' => "+33480805570"
                        ],
                    ]
                ]

            ]
        ]);

    }

    protected function persistMenu()
    {
        $this->messenger->persistMenu([
            [
                "type"    => "postback",
                "title"   => "Help",
                "payload" => "DEVELOPER_DEFINED_PAYLOAD_FOR_HELP"
            ],
            [
                "type"    => "postback",
                "title"   => "Start a New Order",
                "payload" => "DEVELOPER_DEFINED_PAYLOAD_FOR_START_ORDER"
            ],
            [
                "type"  => "web_url",
                "title" => "View Website",
                "url"   => "http://petersapparel.parseapp.com/"
            ]
        ]);
    }

}