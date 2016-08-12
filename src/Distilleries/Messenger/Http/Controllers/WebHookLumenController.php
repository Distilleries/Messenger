<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 31/07/2016
 * Time: 19:09
 */

namespace Distilleries\Messenger\Http\Controllers;

use Distilleries\Messenger\Http\Controllers\Base\WebHookBaseTrait;
use Illuminate\Routing\Controller;

class WebHookLumenController extends \Laravel\Lumen\Routing\Controller
{
    use WebHookBaseTrait;
}