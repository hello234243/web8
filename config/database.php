<?php
$servername = "localhost"; // адрес бд
$username = "root"; // юзерка бд
$password = ""; // пасс от бд
$dbname = "test"; // имя бдшки

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

return $conn;