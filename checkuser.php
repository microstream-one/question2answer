<?php

header('Content-type: text/json');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) or (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')) {
    exit;
}

if (!isset($_POST['uid']) or (!$ms_user_id = intval($_POST['uid']))) {
    exit;
}

require_once __DIR__.'/qa-config.php';
require_once __DIR__.'/qa-include/app/users.php';
require_once __DIR__.'/qa-include/helpers/OutAuth.php';


header('Content-type: text/json;');

$output = ['user' => false];

$connect_qa = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);
if ($connect_qa->connect_errno) {
    echo json_encode($output);
    exit;
}

$result = $connect_qa->query("SELECT `userid`, `handle` FROM `qa_users` WHERE `out_user_id` = {$ms_user_id}", MYSQLI_USE_RESULT);
if (!$result) {
    echo json_encode($output);
    exit;
};

$userObj = $result->fetch_object();
mysqli_free_result($result);

$output = ['user' => false];
if (is_object($userObj) and isset($userObj->userid) and intval($userObj->userid)) {
    $output = ['user' => true];
}


echo json_encode($output);
exit;