<?php
namespace CovidGifts\WP;

use CovidGifts\App\Contracts\Gateway;
use CovidGifts\App\Contracts\Migrations;

class ActivationManager {

    protected $rootFile;
    protected $db;

    public function __construct( $root )
    {
        $this->rootFile = $root;
        \register_activation_hook( $this->rootFile, array(__CLASS__, 'activate'));
        \register_uninstall_hook( $this->rootFile, array(__CLASS__, 'deactivate'));
    }

    public static function activate()
    {
        $db = app()->resolve(Migrations::class);
        $db->createPaymentTable();
        $db->createCertificateTable(); 

        static::setupPaymentGateway();
    }

    public static function deactivate()
    {
        $db = app()->resolve(Migrations::class);
        $db->deleteCertificateTable();
        $db->deletePaymentTable();
    }

    private static function setupPaymentGateway()
    {
        app()->resolve(Gateway::class)->register();
    }
}