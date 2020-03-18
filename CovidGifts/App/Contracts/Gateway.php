<?php
namespace CovidGifts\App\Contracts;

interface Gateway {

    public function createIntent($amount, $currency, $meta);

    public function register();

}