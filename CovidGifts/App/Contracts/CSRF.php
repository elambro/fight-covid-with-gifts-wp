<?php
namespace CovidGifts\App\Contracts;

interface CSRF {

    public function getField();

    public function getData();

    public function check();

}