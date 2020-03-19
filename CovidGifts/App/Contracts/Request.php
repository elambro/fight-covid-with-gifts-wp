<?php
namespace CovidGifts\App\Contracts;

interface Request {

    public function validate();

    public function get($attribute);

    public function handle();

    public function isMethod($test);

}