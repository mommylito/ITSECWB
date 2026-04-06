<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    app_log('auth', 'User logged out', ['user_id' => current_user_id()]);
}

session_unset();
session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect('index.php');
