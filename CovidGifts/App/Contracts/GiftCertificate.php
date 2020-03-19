<?php
namespace CovidGifts\App\Contracts;

use CovidGifts\App\Contracts\Payment;

interface GiftCertificate {

    public function create($attributes);

    public function save();

    public function delete();

    public function update($attributes);

    public function getPayment();

    public function qr();

    public function markUsed();

    public function markCancelled();

    public static function find($id);

    public static function findByCode($code);

    public function createFromPayment(Payment $payment);

}