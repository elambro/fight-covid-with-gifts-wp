<?php
namespace CovidGifts\App\Contracts;

use CovidGifts\App\Contracts\GiftCertificate;

interface BuyerNotification {

    public static function send($buyer, GiftCertificate $giftCertificate);

}