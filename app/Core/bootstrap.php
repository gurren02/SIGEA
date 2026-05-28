<?php

$config = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('SIGEA_SESSION');
    session_start();
}

require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Models/User.php';
require_once APP_PATH . '/Models/Exam.php';
require_once APP_PATH . '/Services/SimplePdf.php';
require_once APP_PATH . '/Services/Mailer.php';
require_once APP_PATH . '/Core/helpers.php';

