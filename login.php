<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// A logged-in user does not need the login page
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $db = getDB();

        $stmt = $db->prepare('SELECT id, name, password_hash FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // password_verify compares the typed password with the stored hash
        if ($user && password_verify($password, $user['password_hash'])) {
            // New session ID after login (blocks session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];

            redirect('dashboard.php');
        }

        // Same message for "no such email" and "wrong password",
        // so attackers cannot find out which emails are registered
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - PHP Todo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 420px; margin: 40px auto; padding: 0 16px; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 18px; padding: 10px 18px; cursor: pointer; }
        .error { background: #fde8e8; color: #9b1c1c; padding: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <h1>Log in</h1>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
    </form>

    <p>No account yet? <a href="register.php">Register</a></p>
    <p><a href="index.php">Back to home</a></p>
</body>
</html>