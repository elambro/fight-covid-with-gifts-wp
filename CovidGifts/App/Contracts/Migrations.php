<?php
namespace CovidGifts\App\Contracts;

interface Migrations {

    public function createPaymentTable();

    public function createCertificateTable();

    public function deletePaymentTable();

    public function deleteCertificateTable();

    
}