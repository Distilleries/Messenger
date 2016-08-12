<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 11/08/2016
 * Time: 11:11
 */

namespace Distilleries\Messenger\Exceptions;

use Log;

class MessengerException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        \Exception::__construct($message, $code, $previous);
    }


}