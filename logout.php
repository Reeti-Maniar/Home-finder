<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/init.php';

session_unset();
session_destroy();
redirectTo('/index.php', 303);
