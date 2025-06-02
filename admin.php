<?php
session_start();
$conn = require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$applicants = [];
$result = $conn->query("SELECT id, full_name, phone, email, birth_date, gender, biography, contract_accepted, login FROM applicants");
while ($row = $result->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT pl.name FROM programming_languages pl JOIN applicant_languages al ON pl.id = al.language_id WHERE al.applicant_id = ?");
    $stmt->bind_param("i", $row['id']);
    $stmt->execute();
    $lang_result = $stmt->get_result();
    $languages = [];
    while ($lang_row = $lang_result->fetch_assoc()) {
        $languages[] = $lang_row['name'];
    }
    $stmt->close();
    $row['languages'] = implode(', ', $languages);
    $applicants[] = $row;
}

// Статистика по языкам
$stats = [];
$result = $conn->query("SELECT pl.name, COUNT(al.applicant_id) as count FROM programming_languages pl LEFT JOIN applicant_languages al ON pl.id = al.language_id GROUP BY pl.id, pl.name");
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        h1 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #ffffff;
            margin-top: 30px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        table {
            width: 100%;
            max-width: 1200px;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        th, td {
            padding: 15px;
            text-align: left;
            color: #ffffff;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        th {
            background: rgba(76, 175, 80, 0.3);
            font-weight: 600;
        }
        tr {
            transition: background 0.3s ease;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        a {
            color: #4CAF50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #ffffff;
        }
        .action {
            margin-right: 15px;
            padding: 5px 10px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.1);
        }
        .action:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .logout {
            display: block;
            text-align: center;
            margin: 20px auto;
            padding: 12px 24px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: #ffffff;
            border-radius: 8px;
            width: 200px;
            transition: all 0.3s ease;
        }
        .logout:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .stats {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <h1>Панель администратора</h1>
    <a href="logout.php" class="logout">Выйти</a>
    <h2>Данные пользователей</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Дата рождения</th>
            <th>Пол</th>
            <th>Языки</th>
            <th>Биография</th>
            <th>Контракт</th>
            <th>Логин</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($applicants as $applicant): ?>
            <tr>
                <td><?php echo htmlspecialchars($applicant['id']); ?></td>
                <td><?php echo htmlspecialchars($applicant['full_name']); ?></td>
                <td><?php echo htmlspecialchars($applicant['phone']); ?></td>
                <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                <td><?php echo htmlspecialchars($applicant['birth_date']); ?></td>
                <td><?php echo htmlspecialchars($applicant['gender']); ?></td>
                <td><?php echo htmlspecialchars($applicant['languages']); ?></td>
                <td><?php echo htmlspecialchars(substr($applicant['biography'], 0, 50)) . (strlen($applicant['biography']) > 50 ? '...' : ''); ?></td>
                <td><?php echo $applicant['contract_accepted'] ? 'Да' : 'Нет'; ?></td>
                <td><?php echo htmlspecialchars($applicant['login']); ?></td>
                <td>
                    <a href="admin_edit.php?id=<?php echo $applicant['id']; ?>" class="action">Редактировать</a>
                    <a href="admin_delete.php?id=<?php echo $applicant['id']; ?>" class="action" onclick="return confirm('Вы уверены, что хотите удалить?')">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2 class="stats">Статистика по языкам программирования</h2>
    <table>
        <tr>
            <th>Язык</th>
            <th>Количество пользователей</th>
        </tr>
        <?php foreach ($stats as $stat): ?>
            <tr>
                <td><?php echo htmlspecialchars($stat['name']); ?></td>
                <td><?php echo htmlspecialchars($stat['count']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>