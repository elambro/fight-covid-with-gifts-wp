<?php namespace CovidGifts;

use CovidGifts\Adapters\WP\CSRF;
use CovidGifts\Adapters\WP\Config;
use CovidGifts\Adapters\WP\Database;
use CovidGifts\Adapters\WP\DatabaseSchema;
use CovidGifts\Adapters\WP\Log;
use CovidGifts\Adapters\WP\Mailer;
use CovidGifts\App\Contracts\CSRF as CSRFInterface;
use CovidGifts\App\Contracts\Config as ConfigInterface;
use CovidGifts\App\Contracts\Database as DatabaseInterface;
use CovidGifts\App\Contracts\DatabaseSchema as DatabaseSchemaInterface;
use CovidGifts\App\Contracts\Log as LogInterface;
use CovidGifts\App\Contracts\Mailer as MailerInterface;
use CovidGifts\App\ServiceProvider;
use CovidGifts\WP\ActivationManager;
use CovidGifts\WP\AdminManager;
use CovidGifts\WP\AjaxManager;
use CovidGifts\WP\ShortcodeManager;


class App extends ServiceProvider {

    protected $bind = [
        DatabaseInterface::class  =>  Database::class
    ];

    // Note that the order here matters.
    // E.g. the Stripe class depends on Config, so config needs to come first
    protected $singletons = [
        ConfigInterface::class          =>  Config::class,
        DatabaseSchemaInterface::class  =>  DatabaseSchema::class,
        MailerInterface::class          =>  Mailer::class,
        LogInterface::class             =>  Log::class,
        CSRFInterface::class            =>  CSRF::class,
    ];

    public function start()
    {
        // app()->config()->register();

        // load activation and uninstall hooks
        new ActivationManager($this->root);

        // load api endpoints
        new AjaxManager();

        // loaa admin mgmt panel
        new AdminManager();

        // load the WP shortcode
        new ShortcodeManager();
    }

}