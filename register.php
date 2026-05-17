
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
    <title>Register - Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create Account</h2>

        <?php if ($error): ?>
            <p style="color:red; font-size:14px; margin-bottom:10px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="auth.php">
            <input type="hidden"   name="action"   value="register">
            <input type="text"     name="name"     placeholder="Full Name" required>
            <input type="email"    name="email"    placeholder="Email"     required>
            <input type="password" name="password" placeholder="Password"  required>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>


</body>
</html>