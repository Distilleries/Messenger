<?php namespace Distilleries\Messenger\Proxies;

class NullMessengerProxy implements \Distilleries\Messenger\Contracts\MessengerProxyContract {

    public function receivedInput($inputName, $inputValue, $messengerUser, $messengerConfig)
    {
        return true;
    }

    public function userHasBeenLinked($messengerUser, $backendUser)
    {
        return true;
    }

    public function variableCreated($messengerUserVariable, $messengerUser) {

    }

    public function doLogic($name, $messengerUser) {

    }

    public function getPlaceholdersArray()
    {
        return [];
    }
}