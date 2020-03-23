<?php

namespace CovidGifts\WP;

use CovidGifts\App\Requests\ConfigFormRequest;
use CovidGifts\WP\AbstractAdminPage;
use CovidGifts\WP\AjaxManager;
use CovidGifts\WP\ModelListTable;
use CovidGifts\WP\ShortcodeManager;

class AdminOptionsPage extends AbstractAdminPage {

    public static $action = 'covid-gifts-options';
    public static $title  = 'Options';
    public static $slug   = 'covid-gifts-options';

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
        echo '<h1>Gift Certificate Options</h1>';
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
