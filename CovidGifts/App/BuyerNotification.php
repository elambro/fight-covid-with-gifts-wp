<?php namespace CovidGifts\App;

use CovidGifts\Container;
use CovidGifts\App\Contracts\BuyerNotification as Contract;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Mailer;

class BuyerNotification implements Contract {

    public static function send($buyer, GiftCertificate $giftCertificate)
    {
        $mailer = app()->resolve(Mailer::class);
        return $mailer->send( $buyer, static::getSubject($giftCertificate), static::getMessage($giftCertificate) );
    }

    private static function getSubject(GiftCertificate $giftCertificate, $company)
    {
        return 'Your Gift Certificate - ' . $giftCertificate->payment_currency . ' ' . $giftCertificate->payment_amount . ' for ' . $company;
    }

    private static function getMessage(GiftCertificate $giftCertificate, $company)
    {
        ob_start();
        ?>
        Here is your gift certificate for <?php echo $company; ?>:

        Amount:  <?php echo $giftCertificate->payment_currency . ' ' . $giftCertificate->payment_amount; ?>

        Code: <?php echo CodeGenerator::format($giftCertificate->order_code); ?>

        <?php
        return ob_get_clean();
    }

}