<?php namespace CovidGifts;

use CovidGifts\Adapters\WP\Config;
use CovidGifts\Adapters\WP\Database;
use CovidGifts\Adapters\WP\DatabaseSchema;
use CovidGifts\Adapters\WP\Log;
use CovidGifts\Adapters\WP\Mailer;
use CovidGifts\App\BuyerNotification;
use CovidGifts\App\CodeGenerator;
use CovidGifts\App\Contracts\BuyerNotification as BuyerNotificationInterface;
use CovidGifts\App\Contracts\CodeGenerator as CodeGeneratorInterface;
use CovidGifts\App\Contracts\Config as ConfigInterface;
use CovidGifts\App\Contracts\Database as DatabaseInterface;
use CovidGifts\App\Contracts\DatabaseSchema as DatabaseSchemaInterface;
use CovidGifts\App\Contracts\Gateway as GatewayInterface;
use CovidGifts\App\Contracts\GiftCertificate as GiftCertificateInterface;
use CovidGifts\App\Contracts\Log as LogInterface;
use CovidGifts\App\Contracts\Mailer as MailerInterface;
use CovidGifts\App\Contracts\Migrations as MigrationsInterface;
use CovidGifts\App\Contracts\Payment as PaymentInterface;
use CovidGifts\App\Contracts\SellerNotification as SellerNotificationInterface;
use CovidGifts\App\GiftCertificate;
use CovidGifts\App\Migrations;
use CovidGifts\App\Payment;
use CovidGifts\App\SellerNotification;
use CovidGifts\App\ServiceProvider;
use CovidGifts\App\Services\Stripe;
use CovidGifts\WP\ActivationManager;
use CovidGifts\WP\AdminManager;
use CovidGifts\WP\AjaxManager;
use CovidGifts\WP\ShortcodeManager;


class App extends ServiceProvider {

    protected $root;

    protected $bind = [
        DatabaseInterface::class                =>  Database::class,
        GiftCertificateInterface::class         =>  GiftCertificate::class,
        PaymentInterface::class                 =>  Payment::class,
    ];

    // Note that the order here matters.
    // E.g. the Stripe class depends on Config, so config needs to come first
    protected $singletons = [
        ConfigInterface::class                  =>  Config::class,
        CodeGeneratorInterface::class           =>  CodeGenerator::class,
        DatabaseSchemaInterface::class          =>  DatabaseSchema::class,
        GatewayInterface::class                 =>  Stripe::class,
        MailerInterface::class                  =>  Mailer::class,
        MigrationsInterface::class              =>  Migrations::class,
        LogInterface::class                     =>  Log::class,
        SellerNotificationInterface::class      =>  SellerNotification::class,
        BuyerNotificationInterface::class       =>  BuyerNotification::class,
    ];

    public function __construct($root)
    {
        $this->root = $root;
        parent::__construct();
    }

    public function start()
    {
        // load activation and uninstall hooks
        new ActivationManager($this->root);

        // load api endpoints
        new AjaxManager();

        // loaa admin mgmt panel
        // new AdminManager(); -- @todo

        // load the WP shortcode
        new ShortcodeManager($this->root);
    }

    public function domain()
    {
        $protocols = array('http://', 'http://www.', 'www.');
        return str_replace($protocols, '', get_site_url());
    }
}