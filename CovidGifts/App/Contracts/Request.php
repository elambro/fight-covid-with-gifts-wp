<?php
namespace CovidGifts\App\Contracts;

interface Request {

    public function validate();

    public function get($attribute);

    public function input();

    public function isMethod($test);

}