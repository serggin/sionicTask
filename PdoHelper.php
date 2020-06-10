<?php

class PdoHelper
{
    private $dbh;
    private $productStmt;
    private $offerStmt;

    function __construct() {
        $dsn = 'mysql:host=localhost;dbname=sionic';
        $username = 'sionic';
        $password = 'sionic';

        try {
            $this->dbh = new PDO($dsn, $username, $password);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->exec("SET NAMES 'utf8'");
            $this->dbh->exec("SET character_set_client = utf8");
        } catch (PDOException $e) {
            throw new Exception($this->formatError($e));
        }
    }

    private function formatError(PDOException $e) {
        return 'PDO Error: '.$e->getMessage();
    }

    public function recordImport($code, $name, $weight, $usage) {
        try {
            $this->getProductStmt()->execute([$code, $name, $weight, $usage, $name, $weight, $usage]);
        } catch (PDOException $e) {
            throw new Exception($this->formatError($e));
        }
    }

    public function recordOffer($city, $code, $quantity, $price) {
        try {
            $this->getOfferStmt()->execute([$city, $code, $quantity, $price, $quantity, $price]);
        } catch (PDOException $e) {
            throw new Exception($this->formatError($e));
        }
    }

    private function getProductStmt() {
        if (!isset($this->productStmt)) {
            $sql = <<<SQL
INSERT IGNORE INTO product (code, name, weight, `usage`)
VALUES (?,?,?,?)
ON DUPLICATE KEY UPDATE name=?, weight=?, `usage`=?
SQL;
            $this->productStmt = $this->dbh->prepare($sql);
        }
        return $this->productStmt;
    }

    private function getOfferStmt() {
        if (!isset($this->offerStmt)) {
            $sql = <<<SQL
INSERT INTO offer (city, code, quantity, price)
VALUES (?,?,?,?)
ON DUPLICATE KEY UPDATE quantity=?, price=?
SQL;
            $this->offerStmt = $this->dbh->prepare($sql);
        }
        return $this->offerStmt;
    }

}