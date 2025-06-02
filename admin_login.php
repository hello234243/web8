<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для администратора</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d9e4f5, #e6e9f0);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 320px;
            text-align: center;
        }
        h1 {
            color: #333333;
            margin-bottom: 25px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: none;
            border-radius: 8px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.2);
            color: #333333;
            transition: all 0.3s ease;
        }
        input::placeholder {
            color: rgba(51, 51, 51, 0.6);
        }
        input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #388e3c, #4caf50);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        button:hover {
            background: linear-gradient(45deg, #2e7d32, #388e3c);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .error {
            color: #ef5350;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Вход для администратора</h1>
        <?php if (isset($_SESSION['admin_error'])): ?>
            <p class="error"><?php echo htmlspecialchars($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></p>
        <?php endif; ?>
        <form action="admin_auth.php" method="POST">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>