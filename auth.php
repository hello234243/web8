<?php
session_start();
$conn = require_once __DIR__ . '/config/database.php';

$login = trim($_POST['login']);
$password = trim($_POST['password']);

if (empty($login) || empty($password)) {
    $_SESSION['login_error'] = 'Введите логин и пароль';
    header('Location: login.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, password_hash FROM applicants WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $stmt->close();
    $conn->close();
    header('Location: edit_form.php');
} else {
    $_SESSION['login_error'] = 'Неверный логин или пароль';
    header('Location: login.php');
}
exit;
?>