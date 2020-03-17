<?php
namespace CovidGifts\App\Contracts;

interface CodeGenerator {

    public function make($attributes);

    public function format($str);

    public function raw($str);

}