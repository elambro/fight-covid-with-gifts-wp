<?php
namespace CovidGifts\App\Contracts;

interface Log {

    public static function debug($message, $array = null);

    public static function log($message, $array = null);

}