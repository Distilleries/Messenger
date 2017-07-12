<?php

namespace Distilleries\Messenger\Console;

use Distilleries\Messenger\Models\MessengerConfig;
use Distilleries\Messenger\Models\MessengerUserProgress;
use Illuminate\Console\Command;

class PlannedMessenger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messenger:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call the planned crontabs';

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
}
