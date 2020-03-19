<?php namespace CovidGifts\App;

use CovidGifts\Container;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Mailer;
use CovidGifts\App\Contracts\SellerNotification as SellerNotificationInterface;

class SellerNotification implements SellerNotificationInterface {

    public static function send(GiftCertificate $giftCertificate, $to = '')
    {
        $mailer = app()->resolve(Mailer::class);
        return $mailer->sendToAdmin(static::getSubject($giftCertificate), static::getMessage($giftCertificate));
    }

    private static function getSubject(GiftCertificate $giftCertificate)
    {
        return 'Gift Certificate Purchased - ' . $giftCertificate->payment_currency . ' ' . $giftCertificate->payment_amount;
    }

    private static function getMessage(GiftCertificate $giftCertificate)
    {
        ob_start();
        ?>
        A new gift certificate was purchased:

        Amount:  <?php echo $giftCertificate->payment_currency . ' ' . $giftCertificate->payment_amount; ?>

        Name:  <?php echo $giftCertificate->user_name; ?>

        Email: <?php echo $giftCertificate->user_email; ?>

        Phone: <?php echo $giftCertificate->user_phone; ?>

        Code: <?php echo CodeGenerator::format($giftCertificate->order_code); ?>


        <?php
        return ob_get_clean();
    }
}