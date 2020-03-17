<?php
namespace CovidGifts\App\Contracts;

interface DatabaseSchema {

    public function run($sql);

    public function getCollation();

    public function getPrefix();
}