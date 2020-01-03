<?php
if (!empty($_POST) or !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    return;
}

session_start();

require_once __DIR__.'/qa-include/qa-base.php';
require_once __DIR__.'/qa-include/app/users.php';
require_once __DIR__.'/qa-include/helpers/OutAuth.php';


if (qa_is_logged_in()) {
    header('Location:/'); // @todo restore location
    exit;
}


if (isset($_GET['r-auth'])) {
    $hash = (string)$_GET['r-auth'];
    if ($hash === 'ms-user') {
        return;
    } elseif (isset($_GET['k']) and ($id = @intval(@hexdec($_GET['k'])))) {
        require_once __DIR__.'/qa-config.php';
        require_once __DIR__.'/qa-include/db/users.php';

        $msAuth = new OutAuth($id, $hash);
        $msAuth->auth();
    }
}

header('Location:/');