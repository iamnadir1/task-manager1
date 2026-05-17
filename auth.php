
<?php
session_start();
require 'db.php';

$action = $_POST['action'] ?? '';
$error  = '';


if ($action === 'register') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashed]);

            $_SESSION['user_id']  = $pdo->lastInsertId();
            $_SESSION['username'] = $name;
            header('Location: index.php');
            exit;
        }
    }

    
    $_SESSION['auth_error'] = $error;
    header('Location: register.php');
    exit;
}


if ($action === 'login') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }

    $_SESSION['auth_error'] = $error;
    header('Location: login.php');
    exit;
}


if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}


if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_destroy();
    header('Location: login.php');
    exit;
}