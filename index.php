<?php
require_once __DIR__ . '/vendor/autoload.php';

use \SqlCvs\SqlCvs;

$sqlCvs = new SqlCvs('mysql:dbname=yii2_basic_tests;host=127.0.0.1', 'root', '');
$table = 'my_table2';

if (!$sqlCvs->isExistTable($table)) {
    $sqlCvs->import($table, 'tests/example.cvs');
}

$sqlCvs->setTable($table);
$row = $sqlCvs->getRandomRow();
if (count($row)) {
    $sqlCvs->update($row['id'], ['status' => 1]);
    echo $row['value'];
}
