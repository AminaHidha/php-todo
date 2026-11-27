<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);
$db = getDB();

// Load the task, but only if it belongs to the logged-in user
$stmt = $db->prepare('SELECT id, title FROM todos WHERE id = :id AND user_id = :uid');
$stmt->execute([':id' => $id, ':uid' => $userId]);
$todo = $stmt->fetch();

if (!$todo) {
    http_response_code(404);
    exit('Task not found.');
}

$error = '';
$title = $todo['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');

    if ($title === '') {
        $error = 'The task cannot be empty.';
    } elseif (mb_strlen($title) > 200) {
        $error = 'A task must be 200 characters or less.';
    } else {
        $stmt = $db->prepare('UPDATE todos SET title = :title WHERE id = :id AND user_id = :uid');
        $stmt->execute([':title' => $title, ':id' => $id, ':uid' => $userId]);
        redirect('dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit task - PHP Todo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 520px; margin: 40px auto; padding: 0 16px; }
        input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 8px; }
        button { margin-top: 14px; padding: 10px 18px; cursor: pointer; }
        .error { background: #fde8e8; color: #9b1c1c; padding: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <h1>Edit task</h1>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= (int) $todo['id'] ?>">
        <?= csrfField() ?>
        <input type="text" name="title" value="<?= e($title) ?>" maxlength="200" required>
        <button type="submit">Save</button>
    </form>

    <p><a href="dashboard.php">Cancel</a></p>
</body>
</html>