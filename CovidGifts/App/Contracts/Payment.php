<?php
namespace CovidGifts\App\Contracts;

interface Payment {

    public function create($attributes);

    public function save();

    public function delete();

    public function update($attributes);

    public function updateStatus($status);

    public function getCertificate();

    public function refund();

    public static function find($id);

}