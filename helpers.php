<?php
declare(strict_types=1);

// Safer session cookie: JavaScript cannot read it, and it is not sent from other sites' forms
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Escape text before printing it in HTML (prevents XSS)
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Send the browser to another page and stop running this script
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

// True if a user is logged in
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

// Put this at the top of any page that needs a logged-in user
// (like @login_required in Django)
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}