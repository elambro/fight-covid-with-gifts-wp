<?php namespace CovidCoupons\App\Services;

use CovidCoupons\App\Contracts\Config;
use CovidCoupons\App\Contracts\Gateway;
use CovidCoupons\App\Exceptions\PaymentException;
use CovidCoupons\App\Exceptions\PaymentNextStep;

class Stripe implements Gateway
{

    public function __construct( Config $config)
    {
        // @todo - Setup API key @config
        \Stripe\Stripe::setApiKey( $config->getStripeSecret() );        
    }

    public function createIntent($amount, $currency, $meta)
    {
        // cvdapp()->debug('Creating stripe intent... for ' . $currency . ' ' . $amount);

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

    public function checkIntent($intent_id, $amount, $currency)
    {

        try {

            $intent = \Stripe\PaymentIntent::retrieve($intent_id);
            // $confirmed = $intent->confirm();
            // cvdapp()->debug('Confirmed:', $confirmed);


        } catch (\Stripe\Exception\ApiErrorException $e) {

            // cvdapp()->debug('Handling api exception:' . $e->getMessage());

            $this->handleException($e);
        }

        // cvdapp()->debug('got passed the intent.');

        if ($intent->amount !== $amount * 100) {
            throw new PaymentException('errors.payment');
        }

        if ($intent->currency !== strtolower($currency)) {
            throw new PaymentException('errors.payment');
        }


        $this->checkForNextAction($intent);

        if ($intent->status !== 'succeeded') {
            throw new PaymentException('errors.payment');
        }

        return $intent;
    }

    public function register($root = null)
    {
        // Move /wp/CovidCoupons/apple-pay-domain-file to be available from 
        // https://example.com/.well-known/apple-developer-merchantid-domain-association
        try {        
            $dir = get_home_path().'.well-known';
            if (!is_dir($dir)) {
                mkdir($dir, 755, true);
            }

            copy( COVID_COUPONS_ROOT . '/apple-pay-domain-file', $dir.'/apple-developer-merchantid-domain-association');
        
        } catch (\Throwable $e) {
            add_action( 'admin_notices', function () {
                $class = 'notice notice-error';
                $message = __( 'Covid Coupon failed to setup your developer file for an Apple Pay button. See ' . 
                    'https://stripe.com/docs/apple-pay/web/v2#going-live', 'sample-text-domain' );
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            });
        }

        // See https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
        \Stripe\ApplePayDomain::create([ 'domain_name' => cvdapp()->domain() ]);
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
        cvdapp()->log('Error: ' . $msg);
        
        if (strpos($msg, 'Invalid API Key') !== false) {
            $trans = 'errors.missing-key';
        } elseif (strpos($msg, 'previously confirmed') !== false ) {
            cvdapp()->log('Previously confirmed...');
            return;
        } else {
            $trans = 'errors.gateway';
        }


        throw new PaymentException($trans, null, $e);
    }


} ?>