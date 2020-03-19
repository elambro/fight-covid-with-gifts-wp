<?php namespace CovidGifts\App;

class SqlManager {

    public function createPaymentTable($table_name, $charset_collate = '')
    {
      return "CREATE TABLE IF NOT EXISTS `{$table_name}` ( 
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_name` varchar(255) DEFAULT NULL,
            `user_email` varchar(255) NOT NULL,
            `user_phone` varchar(255) DEFAULT NULL,
            `payment_token` varchar(255) DEFAULT NULL,
            `payment_meta` text,
            `payment_method` varchar(255) DEFAULT NULL,
            `payment_amount` float DEFAULT NULL,
            `payment_currency` varchar(5) DEFAULT NULL,
            `payment_status` varchar(255) DEFAULT NULL,
            `payment_id` varchar(255) DEFAULT NULL,
            `paid_at` datetime NULL DEFAULT NULL,
            `accepted_at` datetime NULL DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) $charset_collate;";
    }

    public function createCertificateTable($table_name, $charset_collate = '')
    {
      return "CREATE TABLE IF NOT EXISTS `{$table_name}` ( 
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_name` varchar(255) DEFAULT NULL,
        `user_email` varchar(255) DEFAULT NULL,
        `user_phone` varchar(255) DEFAULT NULL,
        `order_meta` text,
        `order_code` varchar(20) NOT NULL UNIQUE,
        `payment_id` int(11) DEFAULT NULL UNIQUE,
        `payment_amount` float DEFAULT NULL,
        `payment_currency` varchar(20) DEFAULT NULL,
        `cancelled_at` datetime DEFAULT NULL,
        `used_at` datetime DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
      ) $charset_collate;";

    }

    public function dropTable($table_name)
    {
      return "DROP TABLE IF EXISTS {$table_name};";
    }

}