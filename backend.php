<?php

require 'PdoHelper.php';

try {
    $pdo = new PdoHelper();
} catch (Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
    exit;
}

if (isset($_REQUEST['total'])) {
    $total = $pdo->getTotal();
    echo $total;
    exit;
}

if (isset($_REQUEST['cities'])) {
    $cities = $pdo->getCities();
    $cities = array_map(function($item) {
        switch($item->city) {
            case 'Москва': $abbr = 'msk'; break;
            case 'Казань': $abbr = 'kaz'; break;
            case 'Новосибирск': $abbr = 'nov'; break;
            case 'Самара': $abbr = 'sam'; break;
            case 'Санкт-Петербург': $abbr = 'spb'; break;
            case 'Саратов': $abbr = 'sar'; break;
            case 'Челябинск': $abbr = 'che'; break;
            case 'Деловые линии Челябинск': $abbr = 'dlc'; break;
            default: $abbr = '';
        }
        $item->abbr = $abbr;
        return $item;
    }, $cities);
    echo json_encode($cities);
    exit;
}

if (isset($_REQUEST['limit'])) {
    $offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
    $data = $pdo->getTableData($offset, $_REQUEST['limit']);
    echo json_encode($data);
    exit;
}
