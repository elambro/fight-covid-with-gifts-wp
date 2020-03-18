<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Exceptions\ValidationException;
use CovidGifts\App\Contracts\Request as RequestInterface;

class UserFormRequest extends Request implements RequestInterface
{

    public function build()
    {
        return [
            'name'   => $this->postedString('u_email'),
            'email'  => $this->postedEmail('u_email'),
            'phone'  => $this->postedString('u_phone'),
            'token'  => $this->postedString('u_token'),
            'amount' => $this->postedFloat('u_amount'),
        ];
    }

    public function validate()
    {
        if (!$this->get('name')) {
            throw new ValidationException('name.required');
        }
        if (!$this->get('phone') && !$this->get('email')) {
            throw new ValidationException('phone_or_email.required');
        }
        if (!$this->get('token')) {
            throw new ValidationException('token.required');
        }

        $amount = $this->get('amount');
        if (!$amount) {
            throw new ValidationException('amount.required');
        }
        $min = app()->resolve(GiftCertificate::class)->getMin();
        if ($amount < $min) {
            throw new ValidationException('amount.min', $min);   
        }
        $max = app()->resolve(GiftCertificate::class)->getMax();
        if ($amount > $max) {
            throw new ValidationException('amount.max', $max);   
        }

        return true;
    }
}