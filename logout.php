<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (logged_in()) {
    audit('LOGOUT', 'User', user()['id'], null, null);
    unset($_SESSION['user']);
}
session_destroy();
redirect('login.php');
