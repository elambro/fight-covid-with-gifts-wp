<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\BuyerNotification;
use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Request as RequestInterface;
use CovidGifts\App\Exceptions\ValidationException;
use CovidGifts\App\SellerNotification;

class PaymentFormRequest extends Request implements RequestInterface
{

    public function build()
    {
        return [
            'intent_id'  => $this->postedString('intent_id'),
            'payment_id' => $this->postedString('payment_id'),
            'method'     => $this->postedString('method'),
            'name'       => $this->postedString('name'),
            'email'      => $this->postedString('email'),
            'phone'      => $this->postedString('phone'),
            'amount'     => $this->postedFloat('amount'),
            'currency'   => $this->postedString('currency'),
            'status'     => $this->postedString('status'),
        ];
    }

    public function handle()
    {
        cvdapp()->csrf()->check();

        $this->validate();

        $certif = $this->createCertificate();

        $intent = $this->verifyPayment();

        $certif->markPaid();

        $email_sent = $this->sendNotifications($certif);

        $config = cvdapp()->config();
        
        return [
            'amount'     => $certif->payment_amount,
            'company'    => $config->getSellerCompanyName(),
            'gift_code'  => $certif->formattedCode(),
            'currency'   => $certif->payment_currency,
            'symbol'     => $config->getCurrencySymbol(),
            'email_sent' => $email_sent ? 1 : 0
        ];
    }

    protected function verifyPayment()
    {
        $input = $this->input();

        return cvdapp()->gateway()->checkIntent($input->intent_id, $input->amount, $input->currency);
    }

    protected function sendNotifications(GiftCertificate $certificate)
    {
       
        $sent = false;

        try {
            
            (new SellerNotification($certificate))->send();
            
            $sent = (new BuyerNotification($certificate))->send();

        } catch (\Exception $e) {
            cvdapp()->log($e->getMessage());
        }

        return $sent;
    }

    protected function createCertificate()
    {
        $input = $this->input();

        return cvdapp()->resolve(GiftCertificate::class)->create([
            'user_name'        => $input->name,
            'user_email'       => $input->email,
            'user_phone'       => $input->phone,        // $res->sender_phone
            'intent_id'        => $input->intent_id,    // liqpay -> create this yourself
            'payment_method'   => $input->method,       // 'liqpay_' . $res->paytype
            'payment_id'       => $input->payment_id,   // $res->payment_id
            'payment_status'   => $input->status,       // $res->status
            'payment_amount'   => $input->amount,
            'payment_currency' => $input->currency,
            // 'payment_meta'
        ]);
    }

    public function validate()
    {
        $required = ['intent_id', 'payment_id', 'method', 'name', 'amount', 'currency', 'status'];
        foreach ($required as $attribute) {
            if (!$this->get($attribute)) {
                throw new ValidationException('validation.' . $attribute . '.required');
            }
        }

        if (!$this->get('phone') && !$this->get('email')) {
            throw new ValidationException('validation.phone_or_email.required');
        }

        $amount = $this->get('amount');
        if (!$amount) {
            throw new ValidationException('validation.amount.required');
        }
        $min = cvdapp()->config()->getMinPayment();
        if ($amount < $min) {
            throw new ValidationException('validation.amount.min', ['min' => $min]);   
        }
        $max = cvdapp()->config()->getMaxPayment();
        if ($amount > $max) {
            throw new ValidationException('validation.amount.max', ['max' => $max]);   
        }

        $currency = strtolower($this->get('currency'));
        if (!$currency) {
            throw new ValidationException('validation.currency.required');
        }
        if (strlen($currency) !== 3 || strtolower(cvdapp()->config()->getCurrency()) !== $currency) {
            throw new ValidationException('validation.currency.valid');
        }

        return true;
    }
}