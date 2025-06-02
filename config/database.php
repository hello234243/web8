<?php
$servername = "localhost";
$username = "nnngggpg_bd";  // ← это твой пользователь
$password = "Algoritm123";          // ← это твой пароль
$dbname = "nnngggpg_bd";    // ← это и имя базы данных

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>
