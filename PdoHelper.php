<?php

class PdoHelper
{
    private $dbh;
    private $productStmt;
    private $offerStmt;
    private $tableDataStmt;

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

    public function getTotal() {
        $sql = <<<SQL
SELECT count(*) AS total FROM product;
SQL;
        return $this->dbh->query($sql)->fetchColumn();
    }

    public function getCities() {
        $sql = <<<SQL
SELECT DISTINCT city FROM offer;
SQL;
        return $this->dbh->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function getTableData($offset, $limit) {
        $stmt = $this->getTableDataStmt();
        $stmt->bindValue(1, $offset,PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    private function getTableDataStmt() {
        if (!isset($this->tableDataStmt)) {
            $sql = <<<SQL
select p.id, p.code, p.name, p.weight, p.usage,
sum(case when city='Москва' then o.quantity else 0 end) as quantity_msk,
sum(case when city='Москва' then o.price else 0 end) as price_msk,
sum(case when city='Казань' then o.quantity else 0 end) as quantity_kaz,
sum(case when city='Казань' then o.price else 0 end) as price_kaz,
sum(case when city='Новосибирск' then o.quantity else 0 end) as quantity_nov,
sum(case when city='Новосибирск' then o.price else 0 end) as price_nov,
sum(case when city='Самара' then o.quantity else 0 end) as quantity_sam,
sum(case when city='Самара' then o.price else 0 end) as price_sam,
sum(case when city='Санкт-Петербург' then o.quantity else 0 end) as quantity_spb,
sum(case when city='Санкт-Петербург' then o.price else 0 end) as price_spb,
sum(case when city='Саратов' then o.quantity else 0 end) as quantity_sar,
sum(case when city='Саратов' then o.price else 0 end) as price_sar,
sum(case when city='Челябинск' then o.quantity else 0 end) as quantity_che,
sum(case when city='Челябинск' then o.price else 0 end) as price_che,
sum(case when city='Деловые линии Челябинск' then o.quantity else 0 end) as quantity_dlc,
sum(case when city='Деловые линии Челябинск' then o.price else 0 end) as price_dlc
from product p
left join offer o using(code)
group by p.code
order by p.id desc
limit ?,?;
SQL;
            $this->tableDataStmt = $this->dbh->prepare($sql);
        }
        return $this->tableDataStmt;
    }

}