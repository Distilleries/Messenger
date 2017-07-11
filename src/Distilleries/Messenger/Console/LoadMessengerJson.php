<?php

namespace App\Console\Commands;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->messenger = app('messenger');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = storage_path() . "/json/messenger.json";
        if (File::exists($path)) {
            $json = json_decode(file_get_contents($path), true);
            if ($json) {
                $this->cleanDatabase();
                $this->configure($json['config']);
                $this->saveStartMessage($json['welcome']);
            }
        }
    }

    protected function configure($conf) {
        if ($conf['start_btn']) {
            $this->messenger->callSendAPI(["get_started" => ["payload" => "GET_STARTED_PAYLOAD"]]);
        } else {
            $this->messenger->callSendAPI(["fields" => ["get_started"]], "DELETE");
        }
    }

    protected function cleanDatabase() {
        MessengerConfig::truncate();
        MessengerUserProgress::truncate();
    }

    protected function saveStartMessage($data) {
        if ($data) {
            $this->messenger->callSendAPI(["greeting" => ["locale" => "default", "text" => $data]]);
        }
    }
}
