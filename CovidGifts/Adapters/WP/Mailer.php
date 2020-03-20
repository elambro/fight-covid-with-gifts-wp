<?php
namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\Mailer as MailerInterface;

class Mailer implements MailerInterface {

    public function send($to, $subject, $message, $headers = '', $attachments = [])
    {
        return \wp_mail($to, $subject, $message, $headers, $attachments);
    }
}