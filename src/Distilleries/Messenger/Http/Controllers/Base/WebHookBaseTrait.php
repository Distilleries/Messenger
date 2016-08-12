<?php

namespace Distilleries\Messenger\Http\Controllers\Base;

/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 12/08/2016
 * Time: 15:10
 */

use Distilleries\Messenger\Contracts\MessengerReceiverContract;
use \Log;
use Illuminate\Http\Request;

trait WebHookBaseTrait
{

    public function getValidHook(Request $request)
    {
        $hub          = $request->input('hub_mode');
        $verify_token = $request->input('hub_verify_token');

        if ($hub === 'subscribe' && urldecode($verify_token) === config('messenger.validation_token')) {
            return response($request->get('hub_challenge'));
        } else {
            return abort(403);
        }
    }


    public function postMessage(Request $request, MessengerReceiverContract $messenger)
    {
        $object = $request->input('object');
        $result = "";

        if (empty($object)) {
            return abort(403);
        }

        if ($object == 'page') {
            $entry = $request->input('entry');
            $entry = json_decode(json_encode($entry), false);

            if (empty($entry) || !is_array($entry)) {
                return abort(403);
            }

            foreach ($entry as $pageEntry) {

                if (!is_array($pageEntry->messaging)) {
                    continue;
                }

                foreach ($pageEntry->messaging as $messagingEvent) {

                    if (!empty($messagingEvent->optin)) {
                        $result .= $messenger->receivedAuthentication($messagingEvent);
                    } else {
                        if (!empty($messagingEvent->message)) {
                            $result .= $messenger->receivedMessage($messagingEvent);
                        } else {
                            if (!empty($messagingEvent->delivery)) {
                                $result .= $messenger->receivedDeliveryConfirmation($messagingEvent);
                            } else {
                                if (!empty($messagingEvent->postback)) {
                                    $result .= $messenger->receivedPostback($messagingEvent);
                                } else {
                                    $result .= $messenger->defaultHookUndefinedAction($messagingEvent);
                                }
                            }
                        }
                    }
                }
            }
        }

        return response($result);
    }

}