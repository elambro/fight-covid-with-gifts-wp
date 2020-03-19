<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\Contracts\BuyerNotification;
use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Contracts\Payment;
use CovidGifts\App\Contracts\Request as RequestInterface;
use CovidGifts\App\Contracts\SellerNotification;
use CovidGifts\App\Exceptions\ValidationException;

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
        $this->validate();

        $input = $this->input();

        app()->resolve(Gateway::class)->checkIntent($input);

        $payment = app()->resolve(Payment::class);

        $payment->create([
            'user_name'        => $input->name,
            'user_email'       => $input->email,
            'user_phone'       => $input->phone,
            // 'payment_token' => $input->payment_id,
            'payment_meta'     => ['intent_id' => $input->intent_id],
            'payment_method'   => $input->method,
            'payment_amount'   => $input->amount,
            'payment_currency' => $input->currency,
            'payment_status'   => $input->status,
            'payment_id'       => $input->payment_id,
            'paid_at'          => date('Y-m-d H:i:s'),
        ]);

        $certificate = app()->resolve(GiftCertificate::class)->createFromPayment($payment);
        

        $email_sent = false;

        try {
            app()->resolve(SellerNotification::class)->send($certificate);
            if ($certificate->user_email) {
                app()->resolve(BuyerNotification::class)->send($certificate->user_email, $certificate);
                $email_sent = true;
            }
        } catch (\Exception $e) {
            app()->log($e->getMessage());
        }
        
        return [
            'amount'    => $payment->amount,
            'gift_code' => $certificate->order_code,
            'currency'  => $payment->currency,
            'email_sent'=> $email_sent ? 1 : 0
        ];
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
        $min = app()->resolve(GiftCertificate::class)->getMin();
        if ($amount < $min) {
            throw new ValidationException('validation.amount.min', ['min' => $min]);   
        }
        $max = app()->resolve(GiftCertificate::class)->getMax();
        if ($amount > $max) {
            throw new ValidationException('validation.amount.max', ['max' => $max]);   
        }

        return true;
    }
}