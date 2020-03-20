<?php
namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\Config as ConfigInterface;
use CovidGifts\WP\AjaxManager;

class Config implements ConfigInterface {

    public static $charge_action   = 'buy_covid_cert';
    public static $intent_action = 'intent_covid_cert';

    private $decoded = ['ss', 'stripe_secret', 'ls', 'liqpay_secret'];

    private $options;

    private $keys = [
        'ss' => 'stripe_secret',
        'sp' => 'stripe_public',
        'lp' => 'liqpay_public',
        'ls' => 'liqpay_secret',
        'da' => 'default_gift_amount',
        'lc' => 'locale',
        'fl' => 'fallback_locale',
        'sc' => 'seller_country',
        'se' => 'seller_email',
        'cn' => 'seller_company_name',
        'cc' => 'currency',
        'cs' => 'currency_symbol',
        'er' => 'email_required',
        'np' => 'min_payment',
        'xp' => 'max_payment',
        'gn' => 'gateway_name',
    ];

    private function secret1()
    {
        return 'my_simple_secret_key';
    }

    private function secret2()
    {
        return 'my_simple_secret_iv';
    }
    public function get($key)
    {
        if (!$this->options) {
            $this->fetch();
        }
        $key = $this->shortKey($key);
        $val = isset($this->options->$key) ? $this->options->$key : null;
        return $this->shouldBeEncoded($key) ? $this->decode($val) : $val;
    }

    public function set($key, $value)
    {
        $key = $this->shortKey($key);
        $val = $this->shouldBeEncoded($key) ? $this->encode($value) : $value;
        $this->options->key = $value;
        return $this;
    }

    public function fill($settings)
    {
        if (!$this->is_admin()) {
            exit;
        }
        foreach ($settings as $key => $val) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function all()
    {
        if (!$this->is_admin()) {
            exit;
        }
        
        if (!$this->options) {
            $this->fetch();
        }

        $all = $this->options;
        $out = [];

        foreach ($all as $key => $value) {
            $out[$this->longKey($key)] = $this->shouldBeEncoded($key) ? $this->decode($value) : $value;
        }
        return $out;
    }

    public function isAdmin()
    {
        return is_admin();
    }

    public function __get($attribute)
    {
        $value = $this->get($attribute);
        $method = 'get'.$this->camelCase($attribute);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return $value;
        }
    }

    public function __set($attribute, $value)
    {
        $method = 'set'.$this->camelCase($attribute);
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            return $this->options[$attribute] = $value;
        }
    }

    public function __call($function, $args)
    {
        if (substr($function, 0,3) === 'get') {
            $str = $this->snakeCase(str_replace('get', '', $function));
            return $this->get($str);
        }

        if (substr($function, 0,3) === 'set') {
            $str = $this->snakeCase(str_replace('set', '', $function));
            return $this->set($str);
        }
    }

    // public function getStripeSecret()
    // {
    //     return $this->get('ss');
    //     // return "sk_test_AOMz4CvcKMNgiqSKfZgYQqGh00jYh7m1PG";
    // }

    // public function setStripeSecret($value)
    // {
    //     return $this->set('ss', $value);
    // }

    // public function getStripePublic()
    // {
    //     return $this->get('sp');
    //     // return 'pk_test_fnQmkNgdCvoyxK206juc1ZTu00nQrZwXbe';
    // }

    // public function setStripePublic($value)
    // {
    //     return $this->set('sp');
    // }

    // public function getLiqpaySecret()
    // {
    //     return $this->get('ls');
    //     // "sandbox_pYoLweqLPya8xDAY0e7xFJX71bKR6KRXqqwqzDtd";
    // }

    // public function getLiqpayPublic()
    // {
    //     return $this->get('lp');
    //     //"sandbox_i99974611430";
    // }

    // public function setLiqpaySecret($value)
    // {
    //     return $this->set('ls', $value);
    //     // "sandbox_pYoLweqLPya8xDAY0e7xFJX71bKR6KRXqqwqzDtd";
    // }

    // public function setLiqpayPublic($value)
    // {
    //     return $this->set('lp', $value);
    //     //"sandbox_i99974611430";
    // }

    // public function getDefaultGiftAmount()
    // {
    //     return $this->get('da');
    // }

    // public function setDefaultGiftAmount($value)
    // {
    //     return $this->set('da');
    // }

    // public function getLocale()
    // {
    //     return $this->get('lc');
    // }

    // public function setLocale($value)
    // {
    //     return $this->set('lc', $value);
    // }

    // public function getFallbackLocale()
    // {
    //     return $this->get('fl');
    // }

    // public function setFallbackLocale($value)
    // {
    //     return $this->set('fl', $value);
    // }

    // public function getSellerCountry()
    // {
    //     return $this->get('sc');
    // }

    // public function setSellerCountry($value)
    // {
    //     return $this->set('sc', $value);
    // }

    public function getSellerEmail()
    {
        return $this->get('se') ?? \get_option('admin_email');
    }

    // public function setSellerEmail($value)
    // {
    //     return $this->set('se', $value);
    // }

    // public function getSellerCompanyName()
    // {
    //     return $this->get('scn');
    // }

    // public function setSellerCompanyName($value)
    // {
    //     return $this->set('scn', $value);
    // }

    // public function getCurrency()
    // {
    //     return $this->get('cc') ?: 'USD';
    // }

    // public function setCurrency($value)
    // {
    //     return $this->set('cc', $value);
    // }

    public function getCurrencySymbol()
    {
        return $this->get('cs') ?: '$';
    }

    // public function setCurrencySymbol($value)
    // {
    //     return $this->set('cs', $value);
    // }

    public function isEmailRequired()
    {
        return $this->get('er') ? true : false;
    }

    public function setIsEmailRequired($value)
    {
        return $this->set('er', $value ? 1 : 0);
    }

    public function getMinPayment()
    {
        return $this->get('np') ?: 1;
    }

    public function setMinPayment($value)
    {
        return $this->set('np', $value);
    }

    public function getMaxPayment()
    {
        return $this->get('xp') ?: 1000;
    }

    public function setMaxPayment($value)
    {
        return $this->set('xp', $value);
    }

    public function debug()
    {
        return WP_DEBUG;
    }

    public function siteUrl()
    {
        return get_site_url();
    }

    public function getIntentEndpoint()
    {
        return $this->ajaxUrl(static::$intent_action);
    }

    public function getChargeEndpoint()
    {
        return $this->ajaxUrl(static::$charge_action);
    }

    public function getGatewayName()
    {
        return $this->get('gn') ?: 'stripe';
    }

    // public function setGatewayName($value)
    // {
    //     return $this->set('gn', $value);
    // }

    protected function ajaxUrl($action)
    {
        return \admin_url( 'admin-ajax.php' ) . "?action={$action}";
    }

    private function encrypt()
    {
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $this->secret1());
        $iv = substr( hash( 'sha256', $this->secret2() ), 0, 16 );
        return base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }

    private function decrypt($string)
    {
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $this->secret1());
        $iv = substr( hash( 'sha256', $this->secret2() ), 0, 16 );
        return openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    private function camelCase($string, $dontStrip = []){
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/^a-z0-9'.implode('',$dontStrip).']+/', ' ',$string))));
    }

    private function snakeCase($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    private function shortKey($key)
    {
        if (in_array($key, $this->keys)) {
            $key = array_search($key, $this->keys);
        }
        return $key;
    }

    private function longKey($key)
    {
        return isset($this->keys[$key]) ? $this->keys[$key] : $key;
    }

    private function shouldBeEncoded($key)
    {
        return in_array($key, $this->decoded);
    }

    private function fetch()
    {
        $opt = json_decode(\get_option('cvdapp'));
        if (!$opt) {
            $keys = array_keys($this->keys);
            $opt = (object)[];
            foreach ($keys as $key) {
                $opt->$key = null;
            }
        }
        $this->options = $opt;
    }

    public function save()
    {
        \update_option('cvdapp', json_encode($options));
        return $this;
    }


}