<?php
namespace CovidGifts\App\Contracts;

interface Mailer {

    public function send($to, $subject, $message, $headers = '', $attachments = []);

    public function sendToAdmin($subject, $message, $to = '', $headers = '', $attachments = []);

}