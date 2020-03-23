<?php namespace CovidGifts\App;

use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Mailer;

class BuyerNotification {

    protected $certificate;

    protected $company;

    public function __construct(GiftCertificate $giftCertificate)
    {
        $this->certificate = $giftCertificate;
        $this->company = cvdapp()->config()->seller_company_name;
    }

    public function send()
    {
        $mailer = cvdapp()->resolve(Mailer::class);

        $to = $this->certificate->user_email;

        if (!$to) {
            return false;
        }

        return $mailer->send( $to, $this->getSubject(), $this->getMessage() );
    }

    private function getSubject()
    {
        return 'Your Gift Certificate - ' . $this->certificate->payment_currency . ' ' . $this->certificate->payment_amount . ' for ' . $this->company;
    }

    private function getMessage()
    {
        ob_start();
        ?>
        Here is your gift certificate for <?php echo $this->company; ?>:

        Amount: <?php echo $this->certificate->payment_currency . ' ' . $this->certificate->payment_amount; ?>

        Code: <?php echo $this->certificate->formattedCode(); ?>

        <?php
        return ob_get_clean();
    }

}