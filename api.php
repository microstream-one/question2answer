<?php
$sp = $_SERVER['SERVER_PROTOCOL'];

if (empty($_POST)) {
    header($sp.' 400 Bad request', true, 500);
    exit;
}

$headers = getallheaders();
if (!isset($headers['Auth']) or !is_string($headers['Auth'])) {
    header($sp.' 401 Unauthorized', true, 401);
    exit;
}

require_once __DIR__.'/qa-include/qa-base.php';

if (!defined('QA_BASE_DIR')) {
    header($sp.' 500 Internal Server Error', true, 500);
    exit;
}
if (!defined('OUT_URL') or (OUT_URL === '')) {
    header($sp.' 500 Internal Server Error', true, 500);
    exit;
}
if (!defined('API_KEY') or (API_KEY === '')) {
    header($sp.' 500 Internal Server Error', true, 500);
    exit;
}

if (!isset($_GET['qa']) or !is_string($_GET['qa']) or !$_GET['qa']) {
    header($sp.' 400 Bad request', true, 400);
    exit;
}


$haKey = $headers['Auth'];
if (API_KEY !== $haKey) {
    header($sp.' 401 Unauthorized', true, 401);
    exit;
}

/*
 * check post-data
 * check user exist
 */
$qa = $_GET['qa'];

if ($qa == 'create-user') {
    if (!isset($_POST['user']) or !is_array($_POST['user'])) {
        header($sp.' 400 Bad request', true, 400);
        exit;
    }
    $userData = $_POST['user'];

    if (!isset($userData['email']) or !isset($userData['handle']) or !isset($userData['passhash']) or !isset($userData['out_user_id'])) {
        header($sp.' 400 Bad request', true, 400);
        exit;
    }

    $email = str_replace(' ', '', $userData['email']);
    $handle = str_replace(['`', '*', '"', "'", '%'], '', $userData['handle']);
    $passhash = str_replace(' ', '', $userData['passhash']);
    $out_user_id = intval($userData['out_user_id']);
    $level = intval($userData['level']);
    $flags = intval($userData['flags']);
    $created = date('Y-m-d H:i:s');
    $createip = '';
    $loggedin = date('Y-m-d H:i:s', date('U'));
    $loginip = '';

    if (!$email or !$handle or !$passhash or !$out_user_id) {
        header($sp.' 400 Bad request', true, 400);
        exit;
    }

    $connect = new mysqli(QA_MYSQL_HOSTNAME, QA_MYSQL_USERNAME, QA_MYSQL_PASSWORD, QA_MYSQL_DATABASE);
    if ($connect->errno) {
        header($sp.' 500 Internal Server Error', true, 500);
        header('Content-type: text/json');
        $response = ['code'=>500, 'message'=>$connect->errno, 'lc'=>__LINE__];
        echo json_encode($response);
        exit;
    }

    $result = $connect->query("SELECT 1 AS handle_exist FROM `qa_users` WHERE `handle` = '{$handle}'", MYSQLI_USE_RESULT);
    $res = $result->fetch_array();
    @mysqli_free_result($result);
    if (is_array($res) and isset($res['handle_exist']) and ($res['handle_exist'] == '1')) {
        // Handle exist
        $response = ['code'=>426, 'message'=>'Handle exist'];
    } else {
        $result = $connect->query("SELECT 1 AS exist FROM `qa_users` WHERE `out_user_id` = {$out_user_id} OR `email` = '{$email}'", MYSQLI_USE_RESULT);
        if ($result and ($res = $result->fetch_array()) and is_array($res) and isset($res['exist']) and ($res['exist'] == '1')) {
            @mysqli_free_result($result);
            $response = ['code'=>200, 'message'=>'User exist'];
        } else {
            $query = "INSERT INTO `qa_users` (`out_user_id`, `email`, `handle`, `passhash`, `level`, `flags`, `created`, `createip`, `loggedin`, `loginip`)
            VALUES ({$out_user_id}, '{$email}', '{$handle}', '{$passhash}', {$level}, {$flags}, '{$created}', '{$createip}', '{$loggedin}', '{$loginip}')";

            if ($connect->query($query) === true) {
                header($sp.' 201 Created', true, 201);
                $response = ['code'=>201, 'message'=>'User created'];
            } else {
                header($sp.' 500 Internal Server Error', true, 500);
                $response = ['code'=>500, 'message'=>$connect->errno, 'lc'=>__LINE__];
                if (DEBUG) {
                    $response['query'] = $query;
                    $response['error'] = $connect->error;
                }
            }
        }
    }

    header('Content-type: text/json');
    echo json_encode($response);
    $connect->close();
    exit;
}