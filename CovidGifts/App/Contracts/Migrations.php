<?php
namespace CovidGifts\App\Contracts;

interface Migrations {

    public function createTable();

    public function deleteTable();
    
}