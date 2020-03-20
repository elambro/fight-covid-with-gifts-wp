<?php

namespace CovidGifts\WP;

use CovidGifts\WP\AbstractAdminPage;

class AdminMainPage extends AbstractAdminPage {

    public static $action = 'covid-gifts-main';
    public static $title  = 'Gift Certificates';
    public static $slug   = 'covid-gifts-main';

    protected $endpoints = [];

    public function handle()
    {

        echo '<div id="cvdapp-admin-main"></div>';

    }
}
