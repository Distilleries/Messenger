<?php namespace Distilleries\Messenger\Contracts;


interface MessengerProxyContract
{

    // This callback is called when an input is received and valid through the regexp
    // If this method returns false, the input will fail
    public function receivedInput($inputName, $inputValue, $messengerUser, $messengerConfig);

    // This callback is called the user has been successfully connected
    public function userHasBeenLinked($messengerUser, $backendUser);

    // This callback is called when a variable is created
    public function variableCreated($messengerUserVariable, $messengerUser);

    public function getPlaceholdersArray();



}