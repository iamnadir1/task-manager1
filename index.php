
<?php
require 'session.php';
require 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$success = '';
$error   = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = $_POST['category_id']      ?? null;
    $due_date    = $_POST['due_date']          ?? null;

    if (!$title) {
        $error = 'Task title is required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, category_id, title, description, due_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $category_id ?: null,
            $title,
            $description ?: null,
            $due_date    ?: null,
        ]);
        $success = 'Task added successfully!';
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['delete'], $user_id]);
    header('Location: index.php');
    exit;
}

if (isset($_GET['complete'])) {
    $stmt = $pdo->prepare("
        UPDATE tasks
        SET status = IF(status = 'completed', 'pending', 'completed')
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([(int)$_GET['complete'], $user_id]);
    header('Location: index.php');
    exit;
}

$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : null;

if ($catFilter) {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name AS category_name
        FROM tasks t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ? AND t.category_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id, $catFilter]);
} else {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name AS category_name
        FROM tasks t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user_id]);
}
$tasks = $stmt->fetchAll();

$cats = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
$cats->execute([$user_id]);
$cats = $cats->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Manager</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<body style="display:flex; flex-direction:column; min-height:100vh;">

<header class="header">
    <div class="logo"> TaskFlow</div>
    <nav>
        <a href="index.php">Dashboard</a>
    </nav>
    <div style="display:flex; align-items:center; gap:12px;">
        <span class="user">Hello, <?= htmlspecialchars($username) ?></span>
        <a href="auth.php?action=logout"
           style="font-size:13px; color:white;"
           onclick="return confirm('Logout?')">Logout</a>
    </div>
</header>


<main class="container">
<main class="container" style="flex:1;">
    
    <section class="card">
        <h2>Add Task</h2>

        <?php if ($error):   ?><p style="color:red;"><?= htmlspecialchars($error)   ?></p><?php endif; ?>
        <?php if ($success): ?><p style="color:green;"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <form method="POST" action="index.php">
            <input  type="text"     name="title"       placeholder="Task title" required>
            <textarea               name="description" placeholder="Description (optional)"></textarea>
            <select                 name="category_id">
                <option value="">Select Category</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input  type="date"     name="due_date">
            <button type="submit">Add Task</button>
        </form>
    </section>

    
<div style="margin-bottom:16px; display:flex; flex-wrap:wrap; gap:8px;">
    <a href="index.php" 
       style="padding:6px 14px; border-radius:20px; text-decoration:none; font-size:13px;
              background:<?= !isset($_GET['cat']) ? 'blue' : 'white' ?>;
              color:<?= !isset($_GET['cat']) ? 'white' : 'black' ?>;">
        
    </a>
    <?php foreach ($cats as $cat): ?>
        <a href="index.php?cat=<?= $cat['id'] ?>"
           style="padding:6px 14px; border-radius:20px; text-decoration:none; font-size:13px;
                  background:<?= (isset($_GET['cat']) && $_GET['cat'] == $cat['id']) ? '#6c63ff' : '#eee' ?>;
                  color:<?= (isset($_GET['cat']) && $_GET['cat'] == $cat['id']) ? '#fff' : '#333' ?>;">
            <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>
    
    <section class="card">
        <h2>Your Tasks</h2>

        <?php if (empty($tasks)): ?>
            <p style="color:#888; padding:12px ;">No tasks yet. Add one above!</p>
        <?php endif; ?>

        <?php foreach ($tasks as $task): ?>
            <div class="task <?= $task['status'] === 'completed' ? 'completed' : '' ?>"
                 data-task-id="<?= $task['id'] ?>">

                <div class="task-info">
                    <h3><?= htmlspecialchars($task['title']) ?></h3>
                    <?php if ($task['description']): ?>
                        <p><?= htmlspecialchars($task['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($task['category_name']): ?>
                        <span class="category <?= strtolower($task['category_name']) ?>">
                            <?= htmlspecialchars($task['category_name']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($task['due_date']): ?>
                        <small style="color:#888; display:block; margin-top:4px;">
                            Due: <?= htmlspecialchars($task['due_date']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="task-actions">
                    <a href="index.php?complete=<?= $task['id'] ?>">
                        <button class="complete" title="Toggle complete">✔</button>
                    </a>
                    <a href="index.php?delete=<?= $task['id'] ?>"
                       onclick="return confirm('Delete this task?')">
                        <button class="delete" title="Delete task">✖</button>
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    </section>

</main>

<footer>
      2026 TaskFlow - Student Task Manager Project
</footer>

</body>
</html>