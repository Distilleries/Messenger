<?php namespace Distilleries\Messenger\Contracts;


interface MessengerProxyContract
{

    // This callback is called when an input is received and valid through the regexp
    // If this method returns false, the input will fail
    public function receivedInput($messengerUser, $input, $messengerConfig);

}