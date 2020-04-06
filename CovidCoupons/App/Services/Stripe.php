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
        
        $success1 = false;
        $success2 = false;
        $success3 = false;

        (new \CovidCoupons\Adapters\WP\Log)->debug('1');

        try {

            (new \CovidCoupons\Adapters\WP\Log)->debug('2');

            $dir = get_home_path().'.well-known';


            (new \CovidCoupons\Adapters\WP\Log)->debug('3');

            if (!is_dir($dir)) {

                (new \CovidCoupons\Adapters\WP\Log)->debug('4');

                if (!mkdir($dir, 755, true)) {

                    (new \CovidCoupons\Adapters\WP\Log)->debug('5');

                    $this->addDirFailedWarning($dir);

                    (new \CovidCoupons\Adapters\WP\Log)->debug('6');
                
                } 

            } 

            $success1 = is_dir($dir);

            if ($success1) {

                (new \CovidCoupons\Adapters\WP\Log)->debug('7');

                $from = COVID_COUPONS_ROOT . '/apple-pay-domain-file';
                $to = $dir.'/apple-developer-merchantid-domain-association';

                if (!file_exists(($to))) {

                    (new \CovidCoupons\Adapters\WP\Log)->debug('8');

                    try {
                        copy( $from, $to);

                        (new \CovidCoupons\Adapters\WP\Log)->debug('9');

                    } catch (\Throwable $e) {
                        $this->copyFileFailedWarning($from, $to);
                        (new \CovidCoupons\Adapters\WP\Log)->debug('10');
                    } catch (\Exception $e) {
                        $this->copyFileFailedWarning($from, $to);
                        (new \CovidCoupons\Adapters\WP\Log)->debug('11');
                    }
                }

                $success2 = file_exists($to);
            }

            

        } catch (\Throwable $e) {
            $this->addDirFailedWarning($dir);
            (new \CovidCoupons\Adapters\WP\Log)->debug('12');
        } catch (\Exception $e) {
            $this->addDirFailedWarning($dir);
            (new \CovidCoupons\Adapters\WP\Log)->debug('13');
        }

        (new \CovidCoupons\Adapters\WP\Log)->debug('14');

        try {            
            // See https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay
            \Stripe\ApplePayDomain::create([ 'domain_name' => cvdapp()->domain() ]);
            $success3 = true;
        } catch (\Throwable $e) {
            $this->addDomainFailedWarning($e->getMessage());
        } catch (\Exception $e) {
            $this->addDomainFailedWarning($e->getMessage());
        }

        (new \CovidCoupons\Adapters\WP\Log)->debug('15');

        cvdapp()->config()->installed = $success1 && $success2 && $success3 ? true : false;
        cvdapp()->config()->save();

        (new \CovidCoupons\Adapters\WP\Log)->debug('16');
    }

    private function addDomainFailedWarning($msg)
    {
        add_action( 'admin_notices', function () use ($msg) {
            $url = 'https://stripe.com/docs/apple-pay/web/v2#going-live';
            echo '<div class="notice notice-warning"><p>Covid Coupon failed to add your domain <strong>' . acvapp()->domain() . '</strong> to Stripe. ';
            echo ' ' . ( $msg ? '('.$msg.') ' : '' );
            echo 'You can do this manually. See <a href="'.$url.'" target="_blank">Stripe Docs</a>.';
            echo '</p></div>';
        });
    }

    private function copyFileFailedWarning($from, $to)
    {
        add_action( 'admin_notices', function () use ($from, $to) {
            $url = 'https://stripe.com/docs/apple-pay/web/v2#going-live';
            echo '<div class="notice notice-warning"><p>Covid Coupon failed to copy your developer file for an Apple Pay button. ';
            echo 'You can do this manually by copying the file at <strong>'.$from.'</strong> to <strong>'.$to.'</strong>. ';
            echo 'See <a href="'.$url.'" target="_blank">Stripe Docs</a>.';
            echo '</p></div>';
        });
    }

    private function addDirFailedWarning($dir)
    {
        add_action( 'admin_notices', function () use ($dir) {
            $url = 'https://stripe.com/docs/apple-pay/web/v2#going-live';
            echo '<div class="notice notice-warning"><p>Covid Coupon failed to create a new directory: <strong>' . $url . '</strong>. ';
            echo 'See <a href="'.$url.'" target="_blank">Stripe Docs</a>.';
            echo '</p></div>';
        });
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