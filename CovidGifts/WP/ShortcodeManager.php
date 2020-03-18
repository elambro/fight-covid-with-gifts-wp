<?php
namespace CovidGifts\WP;

use CovidGifts\WP\AjaxManager;
// use CovidGifts\WP\Captcha;

class ShortcodeManager {

    const SHORTCODE = 'sell-gift-certificates';
    const NONCE_NAME = 'fight-covid-19-shortcode';
    const NONCE_FIELD = 'nonce';
    protected $version = '1.0';

    public function __construct($root)
    {
        $this->root = \plugin_dir_url($root);
        $this->attach_hooks();
    }

    protected function getIntentAction()
    {
        return AjaxManager::$intent_action;
    }

    protected function getSaveAction()
    {
        return AjaxManager::$save_action;
    }

    protected function getEndpoints()
    {
        return [];
    }

    public function enqueue()
    {

        $dependencies = array();
        \wp_enqueue_script( 'CovidGifts', $this->root . 'dist/index.js', $dependencies, $this->version );
        \wp_enqueue_style('Bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', [], '4.4.1');
        $obj = $this->localizeScript();
        if ( $obj ) {
            \wp_localize_script('CovidGifts', 'ajax_object', $obj);
        }
    }

    protected function attach_hooks()
    {

        \add_shortcode(static::SHORTCODE, array($this, 'handle'));
    }

    public function handle( $atts, $content = null ) {

        $this->enqueue();

        return $this->html();

    }

    protected function html()
    {
        return '<div id="app"></div>';
    }

    public static function checkNonce()
    {
        check_ajax_referer(static::NONCE_NAME, static::NONCE_FIELD);
    }

    protected function localizeScript()
    {
        $nonce = wp_create_nonce(static::NONCE_NAME);        
        return [
            'nonce_data'       => $nonce,
            'nonce_field'      => static::NONCE_FIELD,
            'ajax_url'         => \admin_url( 'admin-ajax.php' ),
            'save_action'      => $this->getSaveAction(),
            'intent_action'    => $this->getIntentAction(),
            'endpoint_intent'  => \admin_url( 'admin-ajax.php' ) . '?action=' . $this->getIntentAction(),
            'endpoint_save'    => \admin_url( 'admin-ajax.php' ) . '?action=' . $this->getSaveAction()
        ];
    }

}