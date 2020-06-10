<?php

require 'PdoHelper.php';

if (!extension_loaded('libxml')) {
    exit('Для работы приложения требуется разширение PHP libxml');
}

if (count($argv) >= 1) {
    $dirname = $argv[1];
    if (!is_dir($dirname)) {
        exit('Первый параметр не является допустимым путем к директории');
    }
    //echo substr($dirname, -1). PHP_EOL;;
    if (substr($dirname, -1) !== '/') {
        $dirname .= '/';
    }
} else {
    $dirname = './';
}

libxml_use_internal_errors(true);
//$dbh = getDbh();
try {
    $pdo = new PdoHelper();
} catch (Exception $e) {
    exit($e->getMessage());
}

handleXmls($dirname, 'import*.xml', 'parseImport', $pdo);
handleXmls($dirname, 'offers*.xml', 'parseOffers', $pdo);

/*$files = glob($dirname . 'import*.xml');
if (count($files)) {
    foreach ($files as $file) {
        $xml = simplexml_load_file($file);
        if ($xml === false) {
            echo "Файл $file не является валидным XML файлом" . PHP_EOL;
        } else {
            parseImport($xml);
            break;
        }
    }
} else {
    echo 'Нет файлов вида import*.xml' . PHP_EOL;
}*/

/*$files = glob($dirname . 'offers*.xml');
if (count($files)) {
    foreach ($files as $file) {
        $xml = simplexml_load_file($file);
        if ($xml === false) {
            echo "Файл $file не является валидным XML файлом" . PHP_EOL;
        } else {
            parseOffers($xml);
            break;
        }
    }
} else {
    echo 'Нет файлов вида offers*.xml' . PHP_EOL;
}*/

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
                //break;
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
    //echo $city;
    $cnt = 0;
    foreach ($xml->ПакетПредложений->Предложения->Предложение as $offer) {
        $code = $offer->Код->__toString();
        $quantity = $offer->Количество->__toString();
        $price = $offer->Цены->Цена->ЦенаЗаЕдиницу->__toString();
        //print $code.' = '.$quantity.' = '.$price. PHP_EOL;

        try {
            $pdo->recordOffer($city, $code, $quantity, $price);
            $cnt++;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }

/*        if (--$cnt == 0) {
            break;
        }*/
     }
    return $cnt;
}

function parseImport($xml, $pdo) {
    //$city = getCity($xml);
    $cnt = 0;
    foreach ($xml->Каталог->Товары->Товар as $product) {
        $name = $product->Наименование->__toString();
        $code = $product->Код->__toString();
        $weight = $product->Вес->__toString();
        //$quantity = $product->Вес->__toString();
        //print $name.' = '.$code.' = '.$weight. PHP_EOL;
        $usage = '';
        $interchangeabilities = $product->Взаимозаменяемости->Взаимозаменяемость;
        //print_r($product->Взаимозаменяемости);
        if ($interchangeabilities) {
            foreach ($interchangeabilities as $interchangeability) {
                $usage .= (strlen($usage) ? '|' : '') .
                    $interchangeability->Марка . '-' .
                    $interchangeability->Модель . '-' .
                    $interchangeability->КатегорияТС;
            }
            //print $usage . PHP_EOL;
        }

        try {
            $pdo->recordImport($code, $name, $weight, $usage);
            $cnt++;
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }

/*        if (--$cnt == 0) {
            break;
        }*/
    }
    return $cnt;
}



