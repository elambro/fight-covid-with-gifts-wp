<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Exceptions\ValidationException;
use CovidGifts\App\Contracts\Request as RequestInterface

class IntentFormRequest extends Request implements RequestInterface
{

    public function handle()
    {
        $this->validate();

        $cur = $this->get('currency');
        $amt = $this->get('amount');

        $code = app()->resolve(Gateway::class)->intent( $amt, $cur, $this->get('meta'));
    }

    public function build()
    {
        return [
            'amount'   => $this->postedFloat('u_amount'),
            'currency' => $this->postedString('u_currency'),
            'meta'     => $this->postedArray('u_meta')
        ];
    }

    public function validate()
    {
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

        $curr = $this->get('currency');
        if (!$currency) {
            throw new ValidationException('currency.required');
        }
        if (strlen($currency) !== '3') {
            throw new ValidationException('currency.valid');
        }

        return true;
    }
}