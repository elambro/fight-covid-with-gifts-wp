<?php
namespace CovidGifts\App\Contracts;

use CovidGifts\App\Contracts\GiftCertificate;

interface SellerNotification {

    public static function send(GiftCertificate $giftCertificate);

}