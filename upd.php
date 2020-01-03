<?php

require_once __DIR__.'/qa-include/qa-base.php';

if (!defined('QA_BASE_DIR')) {
    header($sp.' 500 Internal Server Error', true, 500);
    exit;
}

$connect = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);
if ($connect->errno) {
    var_dump($connect->errno, $connect->error);
    exit;
}

$query = "SHOW COLUMNS FROM `qa_users`";
$result = $connect->query($query, MYSQLI_USE_RESULT);
$res = $result->fetch_array();
@mysqli_free_result($result);

echo '<pre>';var_dump($res);