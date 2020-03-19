<?php
namespace CovidGifts\App\Contracts;

interface Config {

    public function get($key);

    public function getStripeSecret();
}