<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$name = $_SESSION['user_name'] ?? 'User';
$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        // CREATE
        $title = trim($_POST['title'] ?? '');

        if ($title === '') {
            $error = 'Please enter a task.';
        } elseif (mb_strlen($title) > 200) {
            $error = 'A task must be 200 characters or less.';
        } else {
            $stmt = $db->prepare('INSERT INTO todos (user_id, title) VALUES (:uid, :title)');
            $stmt->execute([':uid' => $userId, ':title' => $title]);
            redirect('dashboard.php');
        }
    } elseif ($action === 'toggle') {
        // UPDATE: flip done / not done (1 - 0 = 1, 1 - 1 = 0)
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare('UPDATE todos SET is_done = 1 - is_done WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        redirect('dashboard.php');
    } elseif ($action === 'delete') {
        // DELETE
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM todos WHERE id = :id AND user_id = :uid');
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        redirect('dashboard.php');
    }
}

// READ: this user's tasks only, unfinished first, newest first
$stmt = $db->prepare(
    'SELECT id, title, is_done, created_at
     FROM todos
     WHERE user_id = :uid
     ORDER BY is_done ASC, id DESC'
);
$stmt->execute([':uid' => $userId]);
$todos = $stmt->fetchAll();
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
        .add-form { display: flex; gap: 8px; margin: 20px 0; }
        .add-form input[type="text"] { flex: 1; padding: 8px; }
        button { padding: 8px 14px; cursor: pointer; }
        ul { list-style: none; padding: 0; }
        li { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #ddd; }
        li .title { flex: 1; }
        li.done .title { text-decoration: line-through; color: #888; }
        form.inline { margin: 0; }
        .error { background: #fde8e8; color: #9b1c1c; padding: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>Welcome, <?= e($name) ?>!</h1>

        <form class="inline" method="POST" action="logout.php">
            <?= csrfField() ?>
            <button type="submit">Log out</button>
        </form>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="add-form" method="POST" action="dashboard.php">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="What do you need to do?" maxlength="200" required>
        <button type="submit">Add</button>
    </form>

    <?php if (empty($todos)): ?>
        <p>No tasks yet. Add your first one above.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($todos as $todo): ?>
                <li class="<?= $todo['is_done'] ? 'done' : '' ?>">
                    <form class="inline" method="POST" action="dashboard.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                        <button type="submit"><?= $todo['is_done'] ? 'Undo' : 'Done' ?></button>
                    </form>

                    <span class="title"><?= e($todo['title']) ?></span>

                    <a href="edit.php?id=<?= (int) $todo['id'] ?>">Edit</a>

                    <form class="inline" method="POST" action="dashboard.php"
                          onsubmit="return confirm('Delete this task?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $todo['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>