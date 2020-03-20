<?php
namespace CovidGifts\WP;

class ShortcodeManager {

    const SHORTCODE = 'sell-gift-certificates';
    protected $version;

    public function __construct()
    {
        $root = cvdapp_root();
        $this->root = \plugin_dir_url($root);
        $this->version = cvdapp_version();
        $this->attach_hooks();
    }

    public function handle( $atts, $content = null )
    {
        return $this->enqueue()->html();
    }

    protected function enqueue()
    {
        $dependencies = array();
        \wp_enqueue_script( 'CovidGifts', $this->root . 'dist/index.js', $dependencies, $this->version );
        \wp_enqueue_style('Bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', [], '4.4.1');
        $obj = $this->localizeScript();
        if ( $obj ) {
            \wp_localize_script('CovidGifts', 'ajax_object', $obj);
        }
        return $this;
    }

    protected function attach_hooks()
    {
        \add_shortcode(static::SHORTCODE, array($this, 'handle'));
    }

    protected function html()
    {
        return '<div id="cvdapp"></div>';
    }

    protected function localizeScript()
    {
        $conf = cvdapp()->config();
        $csrf = cvdapp()->csrf();

        return [
            'csrf_data'         => $csrf->getData(),
            'csrf_field'        => $csrf->getField(),
            
            'endpoint_intent'   => $conf->getIntentEndpoint(),
            'endpoint_save'     => $conf->getChargeEndpoint(),
            
            'locale'            => $conf->getLocale(),
            'locale_fallback'   => $conf->getFallbackLocale(),
            
            'company'           => $conf->getSellerCompanyName(),
            'default_amount'    => $conf->getDefaultGiftAmount(),
            'seller_country'    => $conf->getSellerCountry(),
            'currency'          => $conf->getCurrency(),
            'currency_symbol'   => $conf->getCurrencySymbol(),
            'stripe_public_key' => $conf->getStripePublic(),
            'email_required'    => $conf->getEmailRequired(),
        ];
    }

}