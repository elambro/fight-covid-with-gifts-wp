<?php namespace CovidGifts\App;

use CovidGifts\Container;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Mailer;
use CovidGifts\App\Contracts\SellerNotification as SellerNotificationInterface;

class SellerNotification implements SellerNotificationInterface {

    public static function send(GiftCertificate $giftCertificate, $to = '')
    {
        $mailer = app()->resolve(Mailer::class);
        return $mailer->sendToAdmin('Gift Certificate', 'New gift certificate was purchased');
    }

}