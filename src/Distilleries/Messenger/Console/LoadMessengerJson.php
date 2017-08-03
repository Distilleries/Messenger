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
                $this->saveStartMessage($json['start']);
                $this->saveCronMessages($json['cron']);
                $this->saveFreeMessages($json['free']);
                $this->saveRecipesMessages($json['recipes']);
                $this->saveDefaultMessages($json['default']);
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
                unset($recipe['name']);
                $this->saveMessengerObject($recipe, "recipes", $recipe['name'], null);
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
                $extra['logic'] = [ "name" => $data['logic']['name'], "workflow" => $logic['case']];
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
            if (array_key_exists('payload', $data['attachment']) && array_key_exists('buttons', $data['attachment']['payload'])) {
                foreach ($data['attachment']['payload']['buttons'] as $key => $button) {
                    if ($button['type'] == 'postback' && array_key_exists('postback', $button)) {
                        $payload = uniqid();
                        if (array_key_exists('payload', $button)) {
                            $payload = $button['payload'];
                        }
                        $this->saveMessengerObject($button['postback'], $type, $groupId, $currentConfig->id, $payload);
                        $data['attachment']['payload']['buttons'][$key]['payload'] = $payload;
                        unset($data['attachment']['payload']['buttons'][$key]['postback']);
                    }
                }
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
}
