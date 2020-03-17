<?php namespace CovidGifts\App;

use CovidGifts\App\GiftCertificate;
use CovidGifts\App\Payment;
use CovidGifts\App\SqlManager;
use CovidGifts\App\Contracts\DatabaseSchema;
use CovidGifts\App\Contracts\Migrations as MigrationsInterface;

class Migrations implements MigrationsInterface {

    private $schema;
    private $sql;

    public function __construct(SqlManager $sql, DatabaseSchema $schema)
    {
        $this->schema = $schema;
        $this->sql = $sql;
    }

    protected function getPaymentTableName()
    {
        return Payment::getTable();
    }

    protected function getCertificateTableName()
    {
        return GiftCertificate::getTable();
    }

    public function createPaymentTable()
    {
        $table = $this->prefix().$this->getPaymentTableName();
        $sql = $this->sql->createPaymentTable($table, $this->collation());
        $this->schema->run($sql);        
    }

    public function createCertificateTable()
    {
        $table = $this->prefix().$this->getCertificateTableName();
        $sql = $this->sql->createCertificateTable($table, $this->collation());
        $this->schema->run($sql);
    }

    public function deletePaymentTable()
    {
        $table = $this->prefix().$this->getPaymentTableName();
        $this->dropTable($table);
    }

    public function deleteCertificateTable()
    {
        $table = $this->prefix().$this->getCertificateTableName();
        $this->dropTable($table);
    }

    private function collation()
    {
        return $this->schema->getCollation();
    }

    private function prefix()
    {
        return $this->schema->getPrefix();
    }

    private function dropTable($table_name)
    {
        $sql = $this->sql->dropTable($table_name);
        $this->schema->run($sql);
    }

    
}