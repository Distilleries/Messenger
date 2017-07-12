<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 01/08/2016
 * Time: 16:30
 */

namespace Distilleries\Messenger\Helpers;

use Carbon\Carbon;
use Distilleries\Messenger\Contracts\MessengerProxyContract;
use Distilleries\Messenger\Contracts\MessengerReceiverContract;
use Distilleries\Messenger\Models\MessengerConfig;
use Distilleries\Messenger\Models\MessengerLog;
use Distilleries\Messenger\Models\MessengerUser;
use Distilleries\Messenger\Models\MessengerUserProgress;
use Distilleries\Messenger\Models\MessengerUserVariable;
use Log;

class Messenger implements MessengerReceiverContract
{

    public $messenger = null;
    public $user = null;
    public $proxy = null;

    /**
     * Messenger constructor.
     * @param null $messenger
     */
    public function __construct()
    {
        $this->messenger = app('messenger');
        $this->proxy = app(MessengerProxyContract::class);
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
        if (property_exists($event->message, 'is_echo') && $event->message->is_echo) {
            Log::info('Was an echo');

            return;
        }

        $message            = $event->message;
        $messageText        = !empty($message->text) ? $message->text : null;
        $messageAttachments = !empty($message->attachments) ? $message->attachments : null;

        $senderID = $event->sender->id;
        $this->getMessengerUser($senderID);
        $isWaitingForInput = $this->isWaitingForInput();
        if ($isWaitingForInput) {
            $this->doActionFromInput($messageText, $event);
        } elseif ($messageText) {
            $this->doActionFromGrammar($messageText, $event);
        } elseif ($messageAttachments) {
            $this->doActionFromAttachment($messageAttachments, $event);

        }

    }

    protected function isWaitingForInput() {
        $lastDiscuss = $this->user->getLatestDiscussion();
        if ($lastDiscuss->extra_converted && (array_key_exists('input', $lastDiscuss->extra_converted))) {
            Log::info("Waiting input");
            return true;
        }
        Log::info("Not waiting input");
        return false;
    }

    protected function getMessengerUser($senderId)
    {
        $user = MessengerUser::where('sender_id', $senderId)->first();
        if (!$user) {
            $profile = $this->messenger->getCurrentUserProfile($senderId);
            $user    = MessengerUser::create(['sender_id' => $senderId, 'last_conversation_date' => Carbon::now(), 'first_name' => $profile->first_name, 'last_name' => $profile->last_name]);
        }
        $user->update([
            'last_conversation_date' => Carbon::now()
        ]);
        $this->user = $user;
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

    protected function handleMessengerConfig($recipientId, $messengerConfig, $silent = false)
    {
        if (!$messengerConfig->parent_id) {
            // Delete previous state of this group of questions
            MessengerUserProgress::with('config')->where('messenger_user_id', $this->user->id)->get()->where('config.group_id', $messengerConfig->group_id)->each(function($todelete) {
                $todelete->delete();
            });
        }
        $count = MessengerUserProgress::where('messenger_user_id', $this->user->id)->where('messenger_config_id', $messengerConfig->id)->count();
        if ($count == 0 && $silent == false) {
            MessengerUserProgress::create([
                'messenger_user_id'   => $this->user->id,
                'messenger_config_id' => $messengerConfig->id,
                'progression_date' => Carbon::now()
            ]);
        }
        $this->messenger->sendData(json_decode($this->handlePlaceholders($messengerConfig->content)), $recipientId);
    }

    public function receivedPostback($event)
    {
        Log::info('Postback: ' . \GuzzleHttp\json_encode($event));
        $senderID = $event->sender->id;
        $this->getMessengerUser($senderID);

        $payload = $event->postback->payload;
        $config  = MessengerConfig::where('payload', $payload)->first();
        if ($config) {
            $this->handleMessengerConfig($senderID, $config);
        } else {
            $user = MessengerUser::where('sender_id', $senderID)->first();
            MessengerLog::create([
                'messenger_user_id' => $user ? $user->id : null,
                'request'           => 'Postback received from facebook',
                'response'          => $payload,
                'inserted_at'       => Carbon::now(),
            ]);
        }
    }

    protected function handlePlaceholders($content)
    {
        $placeholders = ["first_name", "last_name"];
        if ($this->user) {
            foreach ($placeholders as $holder) {
                if ($this->user->{$holder}) {
                    $content = preg_replace('/\{\{' . $holder . '\}\}/', $this->user->{$holder}, $content);
                } else {
                    foreach ($this->user->variables as $var) {
                        if ($var->name == $holder) {
                            $content = preg_replace('/\{\{' . $holder . '\}\}/', $this->user->{$var->value}, $content);
                        }
                    }
                }
            }
        }

        return $content;
    }

    protected function createVariable($discussion, $value) {
        if ($this->user && property_exists($discussion->extra_converted, 'input')) {
            $name = $discussion->extra_converted->input->name;
            if ($name == 'link'){ // Special linker value
                $this->user->update(['link_id' => $value]);
            }
            $oldValue = $this->user->variables()->where('name', $name)->first();
            if ($oldValue) {
                $oldValue->update(['value' => $value]);
            } else {
                MessengerUserVariable::create([
                    'name' => $name,
                    'value' => $value,
                    'messenger_user_id' => $this->user->id
                ]);
            }
        }
    }

    /*---------------------------------------------------------------------------------------------------------------------------------------------*/
    /*---------------------------------------------------------------------------------------------------------------------------------------------*/
    /*---------------------------------------------------------------------------------------------------------------------------------------------*/

    protected function doActionFromGrammar($messageText, $event)
    {
        $senderID      = $event->sender->id;
        $messageConfig = null;
        if (property_exists($event->message, 'quick_reply')) {
            $payload = MessengerConfig::where('payload', $event->message->quick_reply->payload)->first();
            if ($payload && $payload->parent_id) {
                foreach ($this->user->progress as $progress) {
                    if ($progress->config && $progress->config->id == $payload->parent_id) {
                        $messageConfig = $payload;
                        break;
                    }
                }
            } else {
                $messageConfig = $payload;
            }
        }
        // Quick reply or reply detected
        if ($messageConfig) {
            $this->handleMessengerConfig($senderID, $messageConfig);
        }
    }
    protected function doActionFromInput($messageText, $event)
    {
        Log::info('doActionFromInput: ' . $messageText);
        $senderID      = $event->sender->id;
        $discussion = $this->user->getLatestDiscussion();
        if ($discussion->extra_converted->input->regexpr) {
            $regex = '/' . $discussion->extra_converted->input->regexpr . '/';
            if (!preg_match($regex, $messageText)) {
                $this->handleMessengerConfig($senderID, MessengerConfig::getAnswerFromConfig($discussion->id, MessengerConfig::INPUT_ANSWER_FAILED), true);
                return;
            }
        }

        if (!$this->proxy || $this->proxy->receivedInput($discussion->extra_converted->input->name, $messageText, $this->user, $discussion)) {
            $this->createVariable($discussion, $messageText);
            $this->handleMessengerConfig($senderID, MessengerConfig::getAnswerFromConfig($discussion->id, MessengerConfig::INPUT_ANSWER_SUCCESS));
        }
        return;
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