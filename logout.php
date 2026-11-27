<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

// Logging out changes state, so only accept a POST request (not a plain link click)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verifyCsrf();

// 1. Clear the session data
$_SESSION = [];

// 2. Delete the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();

redirect('login.php');