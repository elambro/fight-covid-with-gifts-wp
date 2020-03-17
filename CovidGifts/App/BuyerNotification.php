<?php namespace CovidGifts\App;

use CovidGifts\Container;
use CovidGifts\App\Contracts\BuyerNotification as Contract;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Mailer;

class BuyerNotification implements Contract {

    public static function send($buyer, GiftCertificate $giftCertificate)
    {
        $mailer = app()->resolve(Mailer::class);
        return $mailer->send( $buyer, 'Gift Certificate', 'Your gift certificate');
    }

}