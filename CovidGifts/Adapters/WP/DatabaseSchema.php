<?php namespace CovidGifts\Adapters\WP;

use CovidGifts\App\Contracts\DatabaseSchema as DatabaseSchemaInterface;

class DatabaseSchema implements DatabaseSchemaInterface {

    private $db;

    private function db()
    {
        if (!$this->db) {
            global $wpdb;
            $this->db = $wpdb;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        return $this->db;
    }

    public function run($sql)
    {
        return \dbDelta( $sql );
    }

    public function getCollation()
    {
        return $this->db()->get_charset_collate();
    }

    public function getPrefix()
    {
        return $this->db()->prefix;
    }
}