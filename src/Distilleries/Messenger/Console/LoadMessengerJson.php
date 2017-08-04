<?php

namespace Distilleries\Messenger\Console;

use Distilleries\Messenger\Models\MessengerConfig;
use Distilleries\Messenger\Models\MessengerUserProgress;
use Illuminate\Console\Command;

class LoadMessengerJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load json config into the database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $messenger = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->messenger = app('messenger');
        $path            = storage_path("/json/messenger.json");
        if (\File::exists($path)) {
            $json = json_decode(file_get_contents($path), true);
            if ($json) {
                $this->cleanDatabase();
                $this->loadConfig($json['config']);
                if (key_exists('start', $json)) {
                    $this->saveStartMessage($json['start']);
                }
                if (key_exists('persistent_menu', $json)) {
                    $this->loadPersistentMenu($json['persistent_menu']);
                }
                if (key_exists('cron', $json)) {
                    $this->saveCronMessages($json['cron']);
                }
                if (key_exists('free', $json)) {
                    $this->saveFreeMessages($json['free']);
                }
                if (key_exists('recipes', $json)) {
                    $this->saveRecipesMessages($json['recipes']);
                }
                if (key_exists('default', $json)) {
                    $this->saveDefaultMessages($json['default']);
                }
            }
        }
    }

    protected function loadConfig($conf)
    {
        if ($conf['start_btn']) {
            $this->messenger->callSendAPI(["get_started" => ["payload" => "GET_STARTED_PAYLOAD"]], 'uri_config');
        } else {
            $this->messenger->callSendAPI(["fields" => ["get_started"]], 'uri_config', "DELETE");
        }
        if ($conf['home_text']) {
            $this->messenger->callSendAPI(["greeting" => ["locale" => "default", "text" => $conf['home_text']]], 'uri_config');
        } else {
            $this->messenger->callSendAPI(["fields" => ["greeting"]], 'uri_config', "DELETE");
        }
    }

    protected function loadPersistentMenu($persistentMenu)
    {
        if ($persistentMenu) {
            $this->handlePersistentMenuCallToAction($persistentMenu);
            if (!key_exists('locale', $persistentMenu)) {
                $persistentMenu['locale'] = 'default';
            }
            $this->messenger->callSendAPI(["persistent_menu" =>  [$persistentMenu]], 'uri_config');
        } else {
            $this->messenger->callSendAPI(["fields" => ["persistent_menu"]], 'uri_config', "DELETE");
        }
    }
    protected function handlePersistentMenuCallToAction(&$cta) {
        if (key_exists('postback', $cta)) {
            $payload = uniqid();
            if (array_key_exists('payload', $cta)) {
                $payload = $cta['payload'];
            }
            $this->saveMessengerObject($cta['postback'], 'persistent_menu', 'persistent_menu-' . $payload, null, $payload);
            $cta['payload'] = $payload;
            unset($cta['postback']);
        }
        if (key_exists('call_to_actions', $cta)) {
            foreach ($cta['call_to_actions'] as $k => $menu) {
                $this->handlePersistentMenuCallToAction($cta['call_to_actions'][$k]);
            }
        }
    }

    protected function cleanDatabase()
    {
        MessengerConfig::truncate();
        MessengerUserProgress::truncate();
    }

    protected function saveCronMessages($data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        foreach ($data as $key => $cron) {
            $this->saveMessengerObject($cron, "cron", "cron-" . $key, null);
        }
    }

    protected function saveStartMessage($data)
    {
        $this->saveMessengerObject($data, "start", "start", null, "GET_STARTED_PAYLOAD");
    }

    protected function saveFreeMessages($data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        foreach ($data as $key => $cron) {
            $this->saveMessengerObject($cron, "free", "free-" . $key, null);
        }
    }

    protected function saveDefaultMessages($data)
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        foreach ($data as $key => $cron) {
            $this->saveMessengerObject($cron, "default", "default-" . $key, null);
        }
    }

    protected function saveRecipesMessages($data)
    {
        foreach ($data as $key => $recipe) {
            if (array_key_exists('name', $recipe)) {
                $name = $recipe['name'];
                unset($recipe['name']);
                $this->saveMessengerObject($recipe, "recipes", $name, null);
            }
        }
    }

    protected function saveMessengerObject($data, $type, $groupId = null, $parent_id = null, $payload = null, $extra = [])
    {
        if ($groupId == null) {
            $groupId = uniqid();
        }
        $content = [];
        if (array_key_exists('text', $data)) {
            $content["text"] = $data['text'];
        }

        if (array_key_exists('conditions', $data)) {
            $extra['conditions'] = $data['conditions'];
        }
        if (array_key_exists('keywords', $data)) {
            $extra['keywords'] = $data['keywords'];
        }
        if (array_key_exists('recipe', $data)) {
            $extra['recipe'] = $data['recipe'];
        }
        if (array_key_exists('variable', $data)) {
            $extra['variable'] = $data['variable'];
        }
        if (array_key_exists('logic', $data)) {
            foreach ($data['logic']['workflows'] as $logic) {
                $logicPayload = uniqid();
                if (array_key_exists('payload', $logic)) {
                    $logicPayload = $logic['payload'];
                }
                if (array_key_exists('variable', $logic)) {
                    $extra['variable'] = $logic['variable'];
                }
                $extra['logic'] = ["name" => $data['logic']['name'], "workflow" => $logic['case']];
                if (array_key_exists('postback', $logic)) {
                    $this->saveMessengerObject($logic['postback'], $type, $groupId, $parent_id, $logicPayload, $extra);
                }
            }

            // In case of a logic workflow, the workflow is directly split into multiple
            return;
        }
        $currentConfig = MessengerConfig::create([
            'type'      => $type,
            //'content' => json_encode($content),
            'payload'   => $payload,
            'group_id'  => $groupId,
            'parent_id' => $parent_id
        ]);

        if (array_key_exists('attachment', $data)) {
            if (is_array($data['attachment'])) {
                foreach ($data['attachment'] as $key => $attachment) {
                    if (!is_string($attachment)) {
                        $this->handleAttachment($data['attachment'][$key], $type, $groupId, $currentConfig);
                    }
                }
            } else {
                $this->handleAttachment($data['attachment'], $type, $groupId, $currentConfig);
            }
            $content["attachment"] = $data['attachment'];
        }

        if (array_key_exists('input', $data)) {
            $payload = uniqid();
            if (array_key_exists('payload', $data['input'])) {
                $payload = $data['input']['payload'];
            }
            foreach ([
                         "postback_success"      => MessengerConfig::INPUT_ANSWER_SUCCESS,
                         "postback_failed"       => MessengerConfig::INPUT_ANSWER_FAILED,
                         "postback_unique"       => MessengerConfig::INPUT_ANSWER_FAILED_UNIQUE,
                         "postback_failed_proxy" => MessengerConfig::INPUT_ANSWER_FAILED_PROXY,
                         "postback_exists"       => MessengerConfig::INPUT_ANSWER_FAILED_EXISTS,
                     ] as $key => $postbackType) {
                if (array_key_exists($key, $data['input'])) {
                    $this->saveMessengerObject($data['input'][$key], $type, $groupId, $currentConfig->id, $payload, [MessengerConfig::INPUT_ANSWER_TYPE => $postbackType]);
                    unset($data['input'][$key]);
                }
            }
            $extra['input'] = $data['input'];
            unset($data['input']);
        }
        if (array_key_exists('quick_replies', $data)) {
            $quickReplies = [];
            foreach ($data['quick_replies'] as $quick_reply) {
                $quickReplyPayload = uniqid();
                if (array_key_exists('payload', $quick_reply)) {
                    $quickReplyPayload = $quick_reply['payload'];
                }
                if (array_key_exists('variable', $quick_reply)) {
                    $extra['variable'] = $quick_reply['variable'];
                    unset($quick_reply['variable']);
                }
                if (array_key_exists('postback', $quick_reply)) {
                    $this->saveMessengerObject($quick_reply['postback'], $type, $groupId, $currentConfig->id, $quickReplyPayload);
                    unset($quick_reply['postback']);
                }
                $quick_reply['payload'] = $quickReplyPayload;
                $quickReplies[]         = $quick_reply;
            }
            $content["quick_replies"] = $quickReplies;
        }
        if (array_key_exists('replies', $data)) {
            foreach ($data['replies'] as $reply) {
                $this->saveMessengerObject($reply, $type, $groupId, $currentConfig->id, uniqid());
            }
        }
        $currentConfig->update(['extra' => json_encode($extra)]);
        $currentConfig->update(["content" => json_encode($content)]);
    }

    protected function handleAttachment(&$attachment, $type, $groupId, $currentConfig)
    {
        if (array_key_exists('payload', $attachment)) {
            if (array_key_exists('buttons', $attachment['payload'])) {
                foreach ($attachment['payload']['buttons'] as $key => $button) {
                    $this->handleAttachmentButton($attachment['payload']['buttons'][$key], $type, $groupId, $currentConfig);
                }
            }
            if (array_key_exists('elements', $attachment['payload'])) {
                foreach ($attachment['payload']['elements'] as $keyElem => $element) {
                    if (array_key_exists('image_url', $element)) {
                        if (!filter_var($element['image_url'], FILTER_VALIDATE_URL)) {
                            $attachment['payload']['elements'][$keyElem]['image_url'] = asset($attachment['payload']['elements'][$keyElem]['image_url']);
                        }
                    }
                    if (array_key_exists('buttons', $element)) {
                        foreach ($element['buttons'] as $key => $button) {
                            $this->handleAttachmentButton($attachment['payload']['elements'][$keyElem]['buttons'][$key], $type, $groupId, $currentConfig);
                        }
                    }
                    if (array_key_exists('default_action', $element)) {
                        $this->handleAttachmentButton($attachment['payload']['elements'][$keyElem]['default_action'], $type, $groupId, $currentConfig);
                    }
                }
            }
        }
    }

    protected function handleAttachmentButton(&$button, $type, $groupId, $currentConfig)
    {
        if ($button['type'] == 'postback' && array_key_exists('postback', $button)) {
            $payload = uniqid();
            if (array_key_exists('payload', $button)) {
                $payload = $button['payload'];
            }
            $this->saveMessengerObject($button['postback'], $type, $groupId, $currentConfig->id, $payload);
            $button['payload'] = $payload;
            unset($button['postback']);
        }
    }
}
