<?php
namespace CovidGifts\App\Contracts;

interface CodeGenerator {

    public function make($attributes);

    public static function format($str);

    public static function raw($str);

}