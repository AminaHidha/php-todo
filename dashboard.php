<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

requireLogin();   // not logged in? -> sent to login.php

$name = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - PHP Todo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 0 16px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; }
        button { padding: 8px 14px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>Welcome, <?= e($name) ?>!</h1>

        <form method="POST" action="logout.php">
            <button type="submit">Log out</button>
        </form>
    </div>

    <p>You are logged in. Your todo list will appear here in the next step.</p>
    <p>Your user ID in the session: <?= (int) $_SESSION['user_id'] ?></p>
</body>
</html>