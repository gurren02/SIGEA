<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';
$user = require_login();
redirect(dashboard_path($user['role']));
