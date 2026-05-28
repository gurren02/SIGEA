<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';
session_destroy();
redirect('/index.php');
