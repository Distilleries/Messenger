<?php

namespace Distilleries\Messenger\Console\Commands;

use Carbon\Carbon;
use Distilleries\Messenger\Contracts\MessengerReceiverContract;
use Distilleries\Messenger\Models\MessengerConfig;
use Distilleries\Messenger\Models\MessengerLog;
use Distilleries\Messenger\Models\MessengerUser;
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
        $messenger = app(MessengerReceiverContract::class);
        $crons = MessengerConfig::where('type', 'cron')->get();
        foreach ($crons as $cron) {
            if (property_exists($cron->extra_converted, 'date_field')) {
                MessengerUser::with('variables')->whereNotNull('link')->each(function($messengerUser) use ($cron, $messenger) {
                    if (!$messengerUser->variables->contains('name', $cron->group_id) && $messengerUser->link && property_exists($messengerUser->link, $cron->extra_converted->date_field->field)) {
                        $carbonDate = $messengerUser->link->{$cron->extra_converted->date_field->field}->modify($cron->extra_converted->date_field->modifier);
                        if ($carbonDate <= Carbon::now()) {
                            // Trigger
                            try {
                                $messenger->handleMessengerConfig($messengerUser->sender_id, $cron, true);
                            } catch (\Exception $e) {
                                MessengerLog::create([
                                    'messenger_user_id' => $messengerUser->id,
                                    'request' => json_encode($cron),
                                    'response' => json_encode($e->getMessage()),
                                    'inserted_at' => Carbon::now()]);
                            }
                        }
                    }
                });
            }
            if (property_exists($cron->extra_converted, 'date_time')) {
                $carbonDate = new Carbon($cron->extra_converted->date_time);
                if ($carbonDate <= Carbon::now()) {
                    MessengerUser::with('variables')->each(function($messengerUser) use ($cron, $messenger) {
                        if (!$messengerUser->variables->contains('name', $cron->group_id)) {
                            // Trigger
                            try {
                                $messenger->handleMessengerConfig($messengerUser->sender_id, $cron, true);
                            } catch (\Exception $e) {
                                MessengerLog::create([
                                    'messenger_user_id' => $messengerUser->id,
                                    'request' => json_encode($cron),
                                    'response' => json_encode($e->getMessage()),
                                    'inserted_at' => Carbon::now()]);
                            }
                        }
                    });
                }
            }
        }
    }
}
