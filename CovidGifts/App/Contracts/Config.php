<?php
namespace CovidGifts\App\Contracts;

interface Config {

    public function isAdmin();

    public function get($key);

    public function set($key, $value);

    public function debug();

    public function siteUrl();
}