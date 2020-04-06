<?php

namespace CovidCoupons\WP;

use CovidCoupons\App\Requests\ConfigFormRequest;
use CovidCoupons\WP\AbstractAdminPage;
use CovidCoupons\WP\AjaxManager;
use CovidCoupons\WP\ModelListTable;
use CovidCoupons\WP\ShortcodeManager;

class AdminOptionsPage extends AbstractAdminPage {

    public static $action = 'covid-coupons-options';
    public static $title  = 'Options';
    public static $slug   = 'covid-coupons-options';

    protected $endpoints = [
        'fetch_options' => 'fetchConfigOptions',
        'save_options' => 'saveConfigOptions',
    ];

    protected function enqueue()
    {
        $this->addScript('dist/options.js', $this->getAjaxObject());
        $this->addBootstrap();
    }

    public function handle()
    {
        echo '<h1>CovidCoupons Options</h1>';
        echo '<div style="padding: 30px"><div id="cvdapp"></div></div>';
    }

    public function fetchConfigOptions()
    {
        $options = cvdapp()->config()->all();
        return $this->respond($options);
    }

    public function saveConfigOptions()
    {
        cvdapp()->debug('Saving options...');
        $response = (new ConfigFormRequest)->handle();
        return $this->respond($response);
    }

}
