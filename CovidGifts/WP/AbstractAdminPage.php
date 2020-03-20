<?php

namespace CovidGifts\WP;

abstract class AbstractAdminPage {

	protected $hookSuffix;
    protected $version;
    protected $root;

    public static $action;
    public static $title;
    public static $slug;

    protected $endpoints = [];

	public function __construct()
    {
		// $this->mailAction = $cron->getAction();
        $this->version = cvdapp_version(); 
        $this->root = \plugin_dir_url(cvdapp_root());
        $this->attach_hooks();
	}

    abstract public function handle();

    protected function on($action, $method)
    {
        \add_action('wp_ajax_'. $action, [$this, $method]);
    }

	protected function attach_hooks()
    {
        foreach ($this->endpoints as $action => $method) {
            $this->on($action, $method);
        }
	}

    protected function addScript($path, $obj)
    {
        \add_action( 'admin_enqueue_scripts', function () use ($path, $obj) {
            \wp_enqueue_script( 'CovidGifts'.$path, $this->root . $path, [], $this->version );
            if ( $obj ) {
                \wp_localize_script('CovidGifts'.$path, 'ajax_object', $obj);
            }
        });
        return $this;
    }

    protected function addBootstrap()
    {
        \add_action( 'admin_enqueue_scripts', function () {
            \wp_enqueue_style('Bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', [], '4.4.1');
        });
        return $this;
    }
}
