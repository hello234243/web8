<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Генерация CSRF токена, если его нет
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Подключаемся к бд
$conn = require_once __DIR__ . '/config/database.php';

// Экранируем выводимые данные (XSS защита)
function esc($value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Получаем данные пользователя
$stmt = $conn->prepare("SELECT full_name, phone, email, birth_date, gender, biography, contract_accepted FROM applicants WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Получаем языки пользователя
$stmt = $conn->prepare("SELECT language_id FROM applicant_languages WHERE applicant_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$languages = [];
while ($row = $result->fetch_assoc()) {
    $languages[] = $row['language_id'];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование данных</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="form-container">
    <h1>Редактирование данных</h1>

    <form id="editForm" action="api/save_user.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="form-group">
            <label for="fullName">ФИО:</label>
            <input type="text" id="fullName" name="full_name" value="<?php echo esc($data['full_name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo esc($data['phone']); ?>" required pattern="\d{10,15}">
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?php echo esc($data['email']); ?>" required>
        </div>

        <div class="form-group">
            <label for="birthDate">Дата рождения:</label>
            <input type="date" id="birthDate" name="birth_date" value="<?php echo esc($data['birth_date']); ?>" required>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <label><input type="radio" name="gender" value="male" <?php echo esc($data['gender']) === 'male' ? 'checked' : ''; ?>> Мужчина</label>
            <label><input type="radio" name="gender" value="female" <?php echo esc($data['gender']) === 'female' ? 'checked' : ''; ?>> Женщина</label>
        </div>

        <div class="form-group">
            <label for="languages">Любимый язык программирования:</label>
            <select name="languages[]" id="languages" multiple required>
                <?php
                $all_languages = [
                    1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript', 5 => 'PHP',
                    6 => 'Python', 7 => 'Java', 8 => 'Haskell', 9 => 'Clojure', 10 => 'Prolog',
                    11 => 'Scala', 12 => 'Go'
                ];
                foreach ($all_languages as $value => $name):
                    ?>
                    <option value="<?php echo $value; ?>" <?php echo in_array($value, $languages) ? 'selected' : ''; ?>>
                        <?php echo esc($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="biography">Биография:</label>
            <textarea id="biography" name="biography" required><?php echo esc($data['biography']); ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="contractAccepted" id="contractAccepted" required <?php echo $data['contract_accepted'] ? 'checked' : ''; ?>>
                Я согласен с условиями контракта
            </label>
        </div>

        <button type="submit">Сохранить изменения</button>
    </form>

    <p><a href="logout.php">Выйти</a></p>
</div>

<script>
    document.getElementById('editForm').addEventListener('submit', function (e) {
        // включен ли JS, если нет, то форма уйдёт по классике
        if (!window.fetch) return;

        e.preventDefault(); // Отменяем обычную отправку

        // собираем данные из формы
        const data = {
            full_name: document.getElementById('fullName').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            birth_date: document.getElementById('birthDate').value,
            gender: document.querySelector('input[name="gender"]:checked')?.value,
            languages: Array.from(document.getElementById('languages').selectedOptions).map(o => o.value),
            biography: document.getElementById('biography').value,
            contractAccepted: document.getElementById('contractAccepted').checked,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
        };

        // отправляем данные в асинке
        fetch('api/save_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(res => res.json())
            .then(response => {
                if (response.success) {
                    alert('Данные успешно сохранены!');
                } else {
                    console.error(response.errors);
                    alert('Ошибка: проверь данные');
                }
            });
    });
</script>
</body>
</html>
