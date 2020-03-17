<?php
namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\Log as LogInterface;

class Log implements LogInterface {

    public function __construct()
    {
        // do nothing
    }

    public static function debug($message, $array = null)
    {
        if (static::isDebugging()) {
            static::log($message, $array);
        }
    }

    public static function log($message, $array = null)
    {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
        if ( $array ) {
          error_log( var_export($array, true) );
        }
    }

    public static function isDebugging()
    {
        return true;
    }
}