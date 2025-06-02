<?php
session_start();
$conn = require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $_SESSION['admin_error'] = 'Введите логин и пароль';
        header('Location: admin_login.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_login'] = $login;
            header('Location: admin.php');
            exit;
        }
    }

    $_SESSION['admin_error'] = 'Неверный логин или пароль';
    header('Location: admin_login.php');
    exit;
} else {
    header('Location: admin_login.php');
    exit;
}
