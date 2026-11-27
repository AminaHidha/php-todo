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

// ---------- CSRF protection ----------

// One random secret per session, created the first time it is needed
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Put this inside every POST form (like {% csrf_token %} in Django)
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

// Call this at the start of every POST handler
function verifyCsrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    $real = $_SESSION['csrf_token'] ?? '';

    if (!is_string($sent) || $real === '' || !hash_equals($real, $sent)) {
        http_response_code(419);
        exit('Invalid request (CSRF token mismatch). Go back, refresh the page and try again.');
    }
}