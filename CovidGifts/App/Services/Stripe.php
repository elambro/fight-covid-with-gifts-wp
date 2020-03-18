<?php namespace CovidGifts\App\Services;

use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Exceptions\PaymentException;

class Stripe implements Gateway
{

    public function __construct()
    {
        // @todo - Setup API key @config
        \Stripe\Stripe::setApiKey("sk_live_••••••••••••••••••••••••");        
    }

    public function createIntent($amount, $currency, $meta)
    {
        app()->log('Creating stripe intent... for ' . $currency . ' ' . $amount);

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount'   => $amount,
                'currency' => $currency,
                // Verify your integration in this guide by including this parameter
                'metadata' => ['integration_check' => 'accept_a_payment'],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e)
        {
            app()->log('Error: ' . $e->getMessage() );
            throw new PaymentException('errors.gateway', null, $e);
        }

        return $intent->client_secret;
    }

    public function register()
    {
        // @todo - Move /wp/CovidGifts/apple-pay-domain-file to be available from 
        // https://example.com/.well-known/apple-developer-merchantid-domain-association
        // 
        // See https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay

        \Stripe\ApplePayDomain::create([ 'domain_name' => app()->domain() ]);
    }


} ?>