<?php
/*
  Plugin Name: Gift Certificates - Fight Covid-19
  Version: 1.0
  Plugin URI: http://16personalities.com
  Description: Sell gift certificates from your website
  Author: Erin Lambroschini
  Author URI: http://lambroschini.com
 */

require_once __DIR__ . '/.env.php';
require_once __DIR__ . '/vendor/autoload.php';

\CovidGifts\Adapters\WP\Log::debug('Building app...');

if (!function_exists('app')) {

    function app() {
        return \CovidGifts\App::getInstance(__FILE__);
    }
}


$app = app();

\CovidGifts\Adapters\WP\Log::debug('Starting app...');

$app->start();