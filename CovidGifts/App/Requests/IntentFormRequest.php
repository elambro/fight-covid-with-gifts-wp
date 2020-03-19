<?php namespace CovidGifts\App\Requests;

use CovidGifts\App\Abstracts\Request;
use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Contracts\GiftCertificate;
use CovidGifts\App\Exceptions\ValidationException;
use CovidGifts\App\Contracts\Request as RequestInterface;

class IntentFormRequest extends Request implements RequestInterface
{

    public function handle()
    {
        $this->validate();

        $cur  = $this->get('currency');
        $amt  = $this->get('amount');
        $meta = $this->get('meta');

        $code = app()->resolve(Gateway::class)->createIntent( $amt, $cur, $meta);

        return ['clientSecret' => $code];
    }

    public function build()
    {
        $data = [
            'amount'   => $this->postedFloat('u_amount'),
            'currency' => $this->postedString('u_currency'),
            'meta'     => $this->postedArray('u_meta')
        ];
        app()->debug('Data is ', $data);
        return $data;
    }

    public function validate()
    {
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

        $currency = $this->get('currency');
        if (!$currency) {
            throw new ValidationException('validation.currency.required');
        }
        if (strlen($currency) !== 3) {
            throw new ValidationException('validation.currency.valid');
        }

        return true;
    }
}