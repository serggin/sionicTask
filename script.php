<?php

require 'PdoHelper.php';

if (!extension_loaded('libxml')) {
    exit('Для работы приложения требуется разширение PHP libxml');
}
if (count($argv) > 1) {
    $dirname = $argv[1];
    if (!is_dir($dirname)) {
        exit('Первый параметр не является допустимым путем к директории');
    }
    if (substr($dirname, -1) !== '/') {
        $dirname .= '/';
    }
} else {
    $dirname = './';
}

libxml_use_internal_errors(true);
try {
    $pdo = new PdoHelper();
} catch (Exception $e) {
    exit($e->getMessage());
}

handleXmls($dirname, 'import*.xml', 'parseImport', $pdo);
handleXmls($dirname, 'offers*.xml', 'parseOffers', $pdo);

function handleXmls($dirname, $pattern, $callback, $pdo) {
    $files = glob($dirname . $pattern);
    if (count($files)) {
        foreach ($files as $file) {
            $xml = simplexml_load_file($file);
            if ($xml === false) {
                echo "Файл $file не является валидным XML файлом" . PHP_EOL;
            } else {
                $cnt = $callback($xml, $pdo);
                echo "Файл $file: обработано записей - $cnt" . PHP_EOL;
            }
        }
    } else {
        echo "Нет файлов вида $pattern" . PHP_EOL;
    }
}

function getCity($xml) {
    $classificatorName = $xml->Классификатор->Наименование->__toString();
    preg_match('/Классификатор \((.*)\)/', $classificatorName, $matches);
    return $matches[1];
}

function parseOffers($xml, $pdo) {
    $city = getCity($xml);
    $cnt = 0;
    foreach ($xml->ПакетПредложений->Предложения->Предложение as $offer) {
        $code = $offer->Код->__toString();
        $quantity = $offer->Количество->__toString();
        $price = $offer->Цены->Цена->ЦенаЗаЕдиницу->__toString();

        try {
            $pdo->recordOffer($city, $code, $quantity, $price);
            $cnt++;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
     }
    return $cnt;
}

function parseImport($xml, $pdo) {
    $cnt = 0;
    foreach ($xml->Каталог->Товары->Товар as $product) {
        $name = $product->Наименование->__toString();
        $code = $product->Код->__toString();
        $weight = $product->Вес->__toString();
        $usage = '';
        $interchangeabilities = $product->Взаимозаменяемости->Взаимозаменяемость;
        if ($interchangeabilities) {
            foreach ($interchangeabilities as $interchangeability) {
                $usage .= (strlen($usage) ? '|' : '') .
                    $interchangeability->Марка . '-' .
                    $interchangeability->Модель . '-' .
                    $interchangeability->КатегорияТС;
            }
        }

        try {
            $pdo->recordImport($code, $name, $weight, $usage);
            $cnt++;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
    return $cnt;
}
