<?php namespace App\Services;

class Stripe implements Gateway
{

    public function __construct()
    {
        // @todo - Setup API key @config
        \Stripe\Stripe::setApiKey("sk_live_••••••••••••••••••••••••");        
    }

    public function createIntent($amount, $currency, $meta)
    {
        $intent = \Stripe\PaymentIntent::create([
            'amount'   => $amount,
            'currency' => $currency,
            // Verify your integration in this guide by including this parameter
            'metadata' => ['integration_check' => 'accept_a_payment'],
        ]);
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