<?php namespace CovidGifts\App\Services;

use CovidGifts\App\Contracts\Config;
use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Exceptions\PaymentException;
use CovidGifts\App\Exceptions\PaymentNextStep;

class Stripe implements Gateway
{

    public function __construct( Config $config)
    {
        // @todo - Setup API key @config
        \Stripe\Stripe::setApiKey( $config->getStripeSecret() );        
    }

    public function createIntent($amount, $currency, $meta)
    {
        app()->log('Creating stripe intent... for ' . $currency . ' ' . $amount);

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount'   => $amount * 100,
                'currency' => $currency,
                // Verify your integration in this guide by including this parameter
                'metadata' => ['integration_check' => 'accept_a_payment'],
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->handleException($e);
        }

        return $intent->client_secret;
    }

    public function checkIntent($payload)
    {

        try {

            $intent = \Stripe\PaymentIntent::retrieve($payload->intent_id);
            $confirmed = $intent->confirm();
            app()->log('Confirmed:', $confirmed);


        } catch (\Stripe\Exception\ApiErrorException $e) {

            app()->debug('Handling api exception:' . $e->getMessage());

            $this->handleException($e);
        }

        app()->debug('got passed the intent.');

        if ($intent->amount !== $payload->amount * 100) {
            throw new PaymentException('errors.payment');
        }

        if ($intent->currency !== strtolower($payload->currency)) {
            throw new PaymentException('errors.payment');
        }


        $this->checkForNextAction($intent);

        if ($intent->status !== 'succeeded') {
            throw new PaymentException('errors.payment');
        }

    }

    public function register()
    {
        // @todo - Move /wp/CovidGifts/apple-pay-domain-file to be available from 
        // https://example.com/.well-known/apple-developer-merchantid-domain-association
        // 
        // See https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay

        \Stripe\ApplePayDomain::create([ 'domain_name' => app()->domain() ]);
    }

    private function checkForNextAction($intent)
    {
        if ($intent->status === 'requires_action' && $intent->next_action->type == 'use_stripe_sdk') {
            throw new PaymentNextStep([
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ]);
        }
    }

    private function handleException($e)
    {
        $msg = $e->getMessage();
        app()->log('Error: ' . $msg);
        
        if (strpos($msg, 'Invalid API Key') !== false) {
            $trans = 'errors.missing-key';
        } elseif (strpos($msg, 'previously confirmed') !== false ) {
            app()->log('Previously confirmed...');
            return;
        } else {
            $trans = 'errors.gateway';
        }


        throw new PaymentException($trans, null, $e);
    }


} ?>