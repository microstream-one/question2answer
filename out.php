<?php
if (!isset($_GET['qa']) or !is_string($_GET['qa'])) return;

if (in_array($_GET['qa'], ['ajax', 'image', 'blob']) !== false) {
    return;
}

require_once __DIR__.'/qa-include/qa-base.php';

if (!defined('QA_BASE_DIR')) return;
if (!defined('OUT_URL')) return;


require_once __DIR__.'/qa-include/app/users.php';

if ($_GET['qa'] == 'ms-login') {
    require_once 'auth.php';
} elseif ($_GET['qa'] == 'check-user') {
    require_once 'checkuser.php';
} elseif ($_GET['qa'] == 'login') {
    header('Location: '.OUT_URL.'login');
} elseif ($_GET['qa'] == 'register') {
    header('Location: '.OUT_URL.'signup');
} elseif ($_GET['qa'] == 'feedback') {
    header('Location: '.OUT_URL.'contact');
} elseif ($_GET['qa'] == 'logout') {
    if (qa_is_logged_in()) {
        qa_set_logged_in_user(null);
    }
    header('Location: '.OUT_URL.'logout');
    exit;
} elseif ($_GET['qa'] == 'logout.js') {
    header('Content-type: text/javascript');
    if (qa_is_logged_in()) {
        qa_set_logged_in_user(null);
    }
    echo '//logout';
    exit;
}