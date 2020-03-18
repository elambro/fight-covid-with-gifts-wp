<?php
namespace CovidGifts\WP;

use CovidGifts\WP\AjaxManager;
// use CovidGifts\WP\Captcha;

class ShortcodeManager {

    public static $shortcode = 'sell-gift-certificates';

    protected $nonceName = 'fight-covid-19-shortcode';
    protected $nonceField = 'nonce';
    protected $version = '1.0';

    public function __construct($root)
    {
        $this->root = \plugin_dir_url($root);
        $this->attach_hooks();
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

        \add_shortcode($this::$shortcode, array($this, 'handle'));
    }

    public function handle( $atts, $content = null ) {

        $this->enqueue();

        return $this->html();

    }

    protected function html()
    {
        return '<div id="app"></div>';
    }

    public function checkNonce()
    {
        check_ajax_referer($this->nonceName,$this->nonceField);
    }

    protected function localizeScript()
    {
        $nonce = wp_create_nonce($this->nonceName);        
        return [
            'nonce_data'       => $nonce,
            'nonce_field'      => $this->nonceField,
            'ajax_url'         => \admin_url( 'admin-ajax.php' ),
            'save_action'      => $this->getSaveAction(),
            'endpoint_save'    => \admin_url( 'admin-ajax.php' ) . '?action=' . $this->getSaveAction()
        ];
    }

}