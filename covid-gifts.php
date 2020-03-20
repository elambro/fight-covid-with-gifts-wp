<?php
/*
  Plugin Name: Gift Certificates - Fight Covid-19
  Version: 1.0
  Plugin URI: http://16personalities.com
  Description: Sell gift certificates from your website
  Author: Erin Lambroschini
  Author URI: http://lambroschini.com
 */

// require_once __DIR__ . '/.env.php';
require_once __DIR__ . '/vendor/autoload.php';

if (!function_exists('cvdapp')) {
    function cvdapp()
    {
        return \CovidGifts\App::getInstance(__FILE__);
    }
} 
    elseif ( !(cvdapp() instanceof \CovidGifts\App))
{
    throw new Exception('There is a clash with another plugin. This plugin needs to use function `cvdapp()`');
}

if (!function_exists('cvdapp_version')) {
    function cvdapp_version()
    {
       return '1.0';
    }
}

if (!function_exists('cvdapp_root')) {
    function cvdapp_root()
    {
       return __FILE__;
    }
}

cvdapp()->start();