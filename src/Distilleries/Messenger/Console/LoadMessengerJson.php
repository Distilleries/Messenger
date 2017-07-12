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
        $path = storage_path("/json/messenger.json")  ;
        if (\File::exists($path)) {
            $json = json_decode(file_get_contents($path), true);
            if ($json) {
                $this->cleanDatabase();
                $this->loadConfig($json['config']);
                $this->saveStartMessage($json['start']);
            }
        }
    }

    protected function loadConfig($conf) {
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

    protected function cleanDatabase() {
        MessengerConfig::truncate();
        MessengerUserProgress::truncate();
    }

    protected function saveStartMessage($data) {
        if ($data) {
            $this->saveMessengerObject($data, "start", "start", null, "GET_STARTED_PAYLOAD");
        }
    }

    protected function saveMessengerObject($data, $type, $groupId = null, $parent_id = null, $payload = null, $extra = []) {
        if ($groupId == null) {
            $groupId = uniqid();
        }
        $content = [];
        if (array_key_exists('text', $data)) {
            $content["text"] = $data['text'];
        }
        $currentConfig = MessengerConfig::create([
            'type' => $type,
            //'content' => json_encode($content),
            'payload' => $payload,
            'group_id' => $groupId,
            'parent_id' => $parent_id
        ]);

        if (array_key_exists('attachment', $data)) {
            if (array_key_exists('payload', $data['attachment']) && array_key_exists('buttons', $data['attachment']['payload']) ) {
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
            if (array_key_exists('postback_success', $data['input'])) {
                $this->saveMessengerObject($data['input']['postback_success'], $type, $groupId, $currentConfig->id, $payload, [MessengerConfig::INPUT_ANSWER_TYPE => MessengerConfig::INPUT_ANSWER_SUCCESS]);
                unset($data['input']['postback_success']);
            }
            if (array_key_exists('postback_failed', $data['input'])) {
                $this->saveMessengerObject($data['input']['postback_failed'], $type, $groupId, $currentConfig->id, $payload, [MessengerConfig::INPUT_ANSWER_TYPE => MessengerConfig::INPUT_ANSWER_FAILED]);
                unset($data['input']['postback_failed']);
            }
            $extra['input'] = $data['input'];
            unset($data['input']);
        }
        if (array_key_exists('quick_replies', $data)) {
            $quickReplies = [];
            foreach($data['quick_replies'] as $quick_reply) {
                $quickReplyPayload = uniqid();
                if (array_key_exists('payload', $quick_reply)) {
                    $quickReplyPayload = $quick_reply['payload'];
                }
                if (array_key_exists('postback', $quick_reply)) {
                    $this->saveMessengerObject($quick_reply['postback'], $type, $groupId, $currentConfig->id, $quickReplyPayload);
                    unset($quick_reply['postback']);
                }
                $quick_reply['payload'] = $quickReplyPayload;
                $quickReplies[] = $quick_reply;
            }
            $content["quick_replies"] = $quickReplies;
        }
        if (array_key_exists( 'replies', $data)) {
            foreach($data['replies'] as $reply) {
                $this->saveMessengerObject($reply['postback'], $type, $groupId, $currentConfig->id, uniqid());
            }
        }
        $currentConfig->update(['extra' => json_encode($extra)]);
        $currentConfig->update(["content" => json_encode($content)]);
    }
}
