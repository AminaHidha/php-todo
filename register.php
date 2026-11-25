<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// A logged-in user does not need the register page
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = false;
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > 50) {
        $errors[] = 'Name must be 50 characters or less.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Only touch the database if the input is valid
    if (empty($errors)) {
        $db = getDB();

        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $errors[] = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare(
                'INSERT INTO users (name, email, password_hash)
                 VALUES (:name, :email, :hash)'
            );
            $stmt->execute([
                ':name'  => $name,
                ':email' => $email,
                ':hash'  => $hash,
            ]);

            $success = true;
            $name = '';
            $email = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - PHP Todo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 420px; margin: 40px auto; padding: 0 16px; }
        label { display: block; margin-top: 14px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 18px; padding: 10px 18px; cursor: pointer; }
        .error { background: #fde8e8; color: #9b1c1c; padding: 10px; margin-top: 14px; }
        .success { background: #e6f6ea; color: #1e6b34; padding: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <h1>Create an account</h1>

    <?php if ($success): ?>
        <div class="success">
            Account created successfully. <a href="login.php">Log in now</a>.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password (at least 8 characters)</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Log in</a></p>
    <p><a href="index.php">Back to home</a></p>
</body>
</html>