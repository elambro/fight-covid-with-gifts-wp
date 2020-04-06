<?php

namespace CovidCoupons\WP;

use CovidCoupons\WP\AdminMainPage;
use CovidCoupons\WP\AdminOptionsPage;

class AdminManager {

	protected $hookSuffix;
    protected $root;
    protected $version;

	public function __construct()
    {
        $this->version = cvdapp_version(); 
        $this->root = \plugin_dir_url(cvdapp_root());
        $this->attach_hooks();
	}

	protected function attach_hooks()
    {
		\add_action('admin_init', array($this, 'addMenus'));
        \add_action('init', array($this, 'addMenus'));
	}

    protected function getMainPage()
    {
        return new AdminMainPage($this->root);
    }

    protected function getSubPages()
    {
        return [ new AdminOptionsPage($this->root) ];
    }

	public function addMenus()
    {

        $main = $this->getMainPage();
        $pages = $this->getSubPages();

		\add_action('admin_menu', function () use ($main, $pages) {


			$this->hookSuffix = \add_menu_page($main::$title, $main::$title, 'manage_options', $main::$slug, array($main, 'handle'), 'dashicons-tickets-alt', 50);

            foreach ($pages as $page) {

                \add_submenu_page($main::$slug, $page::$title, $page::$title, 'manage_options', $page::$slug, array($page, 'handle'));
            }
		});

		// \add_action( 'load-'. $this->hookSuffix, array( $this, 'screenOptions' ) );
	}        
}
