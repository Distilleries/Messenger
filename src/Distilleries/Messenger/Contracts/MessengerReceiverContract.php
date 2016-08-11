<?php namespace Distilleries\Messenger\Contracts;


interface MessengerReceiverContract
{


    public function receivedAuthentication($event);

    public function receivedMessage($event);

    public function receivedDeliveryConfirmation($event);

    public function receivedPostback($event);

    public function defaultHookUndefinedAction($event);

}