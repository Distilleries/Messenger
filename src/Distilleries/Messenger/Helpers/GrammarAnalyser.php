<?php
/**
 * Created by PhpStorm.
 * User: mfrancois
 * Date: 02/08/2016
 * Time: 09:43
 */

namespace App\Helpers;


use Illuminate\Support\Str;

class GrammarAnalyser
{

    public static function sayHello($message)
    {
        return Str::contains(strtolower($message), ['hello', 'bonjour', 'salut', '']);
    }

    public static function logoAsking($message)
    {
        return Str::contains(strtolower($message), ['image', 'logo', 'le plus beau']);
    }

    public static function whoIAmAsking($message)
    {
        return Str::contains(strtolower($message), ['qui est tu', 'tu es qui', 'qui tu es', 'who are you']);
    }

    public static function saySocial($message)
    {
        return Str::contains(strtolower($message), ['social']);
    }


}