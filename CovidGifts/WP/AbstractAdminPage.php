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
        $this->enqueueScripts();
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

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    private function isHook($hook)
    {
        return $this->endsWith($hook, static::$slug);
    }

    protected function enqueueScripts()
    {
        \add_action( 'admin_enqueue_scripts', function ($hook) {
            if ($this->isHook($hook)) {
                $this->enqueue();
            }
        });
        return $this;
    }

    protected function addScript($path, $obj)
    {
        \wp_enqueue_script( 'CovidGifts'.$path, $this->root . $path, [], $this->version );
        if ( $obj ) {
            \wp_localize_script('CovidGifts'.$path, 'ajax_object', $obj);
        }
        return $this;
    }

    protected function addBootstrap()
    {
        \wp_enqueue_style('Bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css', [], '4.4.1');
        return $this;
    }

    protected function respond($data)
    {
        \wp_send_json_success($data);
        \wp_die();
    }

    protected function ajaxUrl($action)
    {
        return \admin_url( 'admin-ajax.php' ) . "?action={$action}";
    }

    protected function getAjaxObject()
    {
        $var = [];
        foreach ($this->endpoints as $action => $handler) {
            $var[$action] = $this->ajaxUrl($action);
        }

        // $conf = cvdapp()->config();
        $csrf = cvdapp()->csrf();

        $var['csrf_field'] = $csrf->getField();
        $var['csrf_data'] = $csrf->getData();

        return $var;
    }
}
