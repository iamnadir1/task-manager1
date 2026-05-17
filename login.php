
<?php
session_start();


if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>Login</h2>

        <?php if ($error): ?>
            <p style="color:#e74c3c; font-size:14px; margin-bottom:10px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="auth.php">
            <input type="hidden" name="action" value="login">
            <input type="email"    name="email"    placeholder="Email"    required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</div>


</body>
</html>