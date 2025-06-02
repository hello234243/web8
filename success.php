<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/home/b/b918347x/public_html/php_errors.log');

session_start();

error_log("Session data: " . print_r($_SESSION, true));

$success = isset($_SESSION['success']) ? $_SESSION['success'] : false;
$generated_login = isset($_SESSION['generated_login']) ? $_SESSION['generated_login'] : null;
$generated_password = isset($_SESSION['generated_password']) ? $_SESSION['generated_password'] : null;

unset($_SESSION['success'], $_SESSION['generated_login'], $_SESSION['generated_password']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Успешно!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0e7ff, #f3e8ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 20px;
            font-size: 28px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        p {
            font-size: 16px;
            color: #333333;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .credentials {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .credentials p {
            margin: 10px 0;
            font-size: 15px;
        }
        .credentials strong {
            color: #1b5e20;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(45deg, #388e3c, #4caf50);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        a:hover {
            background: linear-gradient(45deg, #2e7d32, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .error {
            color: #ef5350;
            font-size: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Успешно!</h1>
        <p>Ваши данные были сохранены. Спасибо за отправку формы!</p>
        <?php if ($success && $generated_login && $generated_password): ?>
            <div class="credentials">
                <p><strong>Ваш логин:</strong> <?php echo htmlspecialchars($generated_login); ?></p>
                <p><strong>Ваш пароль:</strong> <?php echo htmlspecialchars($generated_password); ?></p>
                <p>Сохраните эти данные для входа и редактирования формы.</p>
            </div>
        <?php else: ?>
            <p class="error">Ошибка: логин и пароль не получены. Проверьте логи.</p>
        <?php endif; ?>
        <a href="index.php">⬅ Вернуться назад</a>
        <a href="login.php">Войти для редактирования</a>
    </div>
</body>
</html>