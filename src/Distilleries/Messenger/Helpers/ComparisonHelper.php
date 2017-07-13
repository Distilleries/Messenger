<?php
namespace Distilleries\Messenger\Helpers;

class ComparisonHelper {
    static function condition ($var1, $op, $var2) {
        switch ($op) {
            case "=":  return $var1 == $var2;
            case "!=": return $var1 != $var2;
            case ">=": return $var1 >= $var2;
            case "<=": return $var1 <= $var2;
            case ">":  return $var1 >  $var2;
            case "<":  return $var1 <  $var2;
            default:       return true;
        }
    }
}