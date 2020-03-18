<?php namespace CovidGifts\App;

use CovidGifts\App\Container;
use CovidGifts\App\Contracts\Log;

class ServiceProvider {

    protected $bind = [];
    protected $singletons = [];
    protected static $instance;

    public $container;

    public function __construct()
    {

        \CovidGifts\Adapters\WP\Log::debug('Creating SP');

        $this->container = Container::getInstance();
        
        \CovidGifts\Adapters\WP\Log::debug('binding');
        foreach ($this->bind as $interface => $class) {
            $this->container->bind($interface, $class);
        }

        \CovidGifts\Adapters\WP\Log::debug('singletons');
        foreach ($this->singletons as $interface => $class) {
            $this->container->singleton($interface, $class);
        }

        \CovidGifts\Adapters\WP\Log::debug('done.');
    }

    public function resolve($class)
    {
        return $this->container->resolve($class);
    }

    public function log($message, $array = null)
    {
        return $this->resolve(Log::class)->log($message, $array);
    }

    public function debug($message, $array = null)
    {
        return $this->resolve(Log::class)->debug($message, $array);
    }

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance($root)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($root);
        }

        return static::$instance;
    }



}