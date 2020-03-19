<?php
namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\Config as ConfigInterface;

class Config implements ConfigInterface {

    public function get($key) 
    {
        // @todo
    }

    public function getStripeSecret()
    {
        // @todo
        return "sk_test_AOMz4CvcKMNgiqSKfZgYQqGh00jYh7m1PG";
    }

}