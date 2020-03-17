<?php namespace CovidGifts\App;

use CovidGifts\App\Abstracts\AbstractModel;
use CovidGifts\App\Contracts\Model;
use CovidGifts\App\Contracts\Payment as PaymentInterface;
use CovidGifts\App\GiftCertificate;

class Payment extends AbstractModel implements PaymentInterface, Model
{
    protected $table = 'c19_payments';

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    public function getCertificate()
    {
        GiftCertificate::find($this->certificate_id);
    }

    public function refund()
    {
        // ...
    }
}