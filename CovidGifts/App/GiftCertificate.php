<?php namespace CovidGifts\App;

use CovidGifts\App\Abstracts\AbstractModel;
use CovidGifts\App\Contracts\CodeGenerator;
use CovidGifts\App\Contracts\GiftCertificate as GiftCertificateInterface;
use CovidGifts\App\Contracts\Model;
use CovidGifts\App\Contracts\Payment;

class GiftCertificate extends AbstractModel implements GiftCertificateInterface, Model {

    protected $table = 'c19_gift_certificates';

    public function getPayment()
    {
        if ($this->payment_id) {
            return app()->resolve(Payment::class)->find($this->payment_id);
        }
    }

    public function getMin()
    {
        return 1; // @todo @config
    }

    public function getMax()
    {
        return 1000; // @todo @config
    }

    public function qr()
    {
        // Generate QR from $this->code @todo @qr
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

    public function createFromPayment(Payment $payment)
    {
        return $this->create([
            'user_name' => $payment->user_name,
            'user_email' => $payment->user_email,
            'user_phone' => $payment->user_phone,
            'order_code' => app()->resolve(CodeGenerator::class)->random(),
            'payment_id' => $payment->id,
            'payment_amount' => $payment->payment_amount,
            'payment_currency' => $payment->payment_currency
        ]);
    }

}