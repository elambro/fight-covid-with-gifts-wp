<?php namespace CovidGifts\App;

use CovidGifts\App\Abstracts\AbstractModel;
use CovidGifts\App\Contracts\GiftCertificate as GiftCertificateInterface;
use CovidGifts\App\Contracts\Model;

class GiftCertificate extends AbstractModel implements GiftCertificateInterface, Model {

    protected $table = 'c19_gift_certificates';

    public function getPayment()
    {
        if ($this->payment_id) {
            return Payment::find($this->payment_id);
        }
    }

    public function qr()
    {
        // Generate QR from $this->code
    }

    public function markUsed()
    {
        $this->update(['used_at' => date('Y-m-d H:i:s')]);
    }

    public function markCancelled()
    {
        $this->update(['cancelled_at' => date('Y-m-d H:i:s')]);
    }

    public static function findByCode($code)
    {
        $result = static::DB()->findBy('code', $code);
        return $result ? new static( $result ) : null;
    }

}