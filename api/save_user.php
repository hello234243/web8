<?php
session_start();
header('Content-Type: application/json'); // для респонса в JSON

// Проверка, авторизован ли пользователь — если нет, то ошибку прокидываем
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'errors' => ['auth' => 'Неавторизован']]);
    exit;
}

// Подключаемся к бд
$conn = require_once __DIR__ . '/../config/database.php';

// Читаем JSON из тела запроса (то, что отправили с фронта)
$data = json_decode(file_get_contents('php://input'), true);

// Сюда будем записывать ошибки, если что-то пойдёт не так
$errors = [];

// Функция для проверки полей формы — чекаем всё, что нужно
function validate($data, &$errors) {
    // ФИО — только буквы и пробелы
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $data['full_name'] ?? '')) {
        $errors['full_name'] = 'ФИО должно содержать только буквы и пробелы';
    }

    // Телефон — только цифры, длина от 10 до 15
    if (!preg_match("/^\d{10,15}$/", $data['phone'] ?? '')) {
        $errors['phone'] = 'Телефон должен содержать только цифры (от 10 до 15 цифр)';
    }

    // Email должен быть нормальным
    if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите правильный e-mail';
    }

    // Дата рождения обязательно
    if (empty($data['birth_date'])) {
        $errors['birth_date'] = 'Дата рождения обязательна';
    }

    // Пол — только male или female
    if (!in_array($data['gender'] ?? '', ['male', 'female'])) {
        $errors['gender'] = 'Укажите пол';
    }

    // Должен быть хотя бы 1 язык программирования
    if (empty($data['languages']) || !is_array($data['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    }

    // Биография — тоже обязательна
    if (empty($data['biography'])) {
        $errors['biography'] = 'Биография обязательна';
    }

    // Должен быть флажок про согласие с контрактом
    if (empty($data['contractAccepted'])) {
        $errors['contractAccepted'] = 'Нужно согласиться с контрактом';
    }

    // Проверка CSRF токена — защита от фейковых запросов
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Проблема с CSRF-токеном';
    }
}

// Проверяем всё, что пользователь отправил
validate($data, $errors);

// Если что-то не так — возвращаем ошибки
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Тут дополнительно проверим, есть ли вообще такой пользователь в бд
$checkStmt = $conn->prepare("SELECT id FROM applicants WHERE id = ?");
$checkStmt->bind_param("i", $_SESSION['user_id']);
$checkStmt->execute();
$result = $checkStmt->get_result();
$checkStmt->close();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'errors' => ['auth' => 'Пользователь не найден или сессия устарела']]);
    exit;
}

// Всё ок — вытаскиваем данные и подготавливаем для сохранения
$fullName = trim($data['full_name']);
$phone = trim($data['phone']);
$email = trim($data['email']);
$birthDate = trim($data['birth_date']);
$gender = $data['gender'];
$biography = trim($data['biography']);
$contractAccepted = $data['contractAccepted'] ? 1 : 0;
$languages = $data['languages'];

// Обновляем инфу о пользователе в бд
$stmt = $conn->prepare("UPDATE applicants SET full_name = ?, phone = ?, email = ?, birth_date = ?, gender = ?, biography = ?, contract_accepted = ? WHERE id = ?");
$stmt->bind_param("ssssssii", $fullName, $phone, $email, $birthDate, $gender, $biography, $contractAccepted, $_SESSION['user_id']);

if ($stmt->execute()) {
    // Сначала чистим старые языки
    $stmt = $conn->prepare("DELETE FROM applicant_languages WHERE applicant_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();

    // Потом добавляем те, что выбрал пользователь
    $stmt = $conn->prepare("INSERT INTO applicant_languages (applicant_id, language_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $language_id);

    foreach ($languages as $language_id) {
        $stmt->execute();
    }

    // закрываем соединение
    $stmt->close();
    $conn->close();

    // отправляем ответ
    echo json_encode(['success' => true]);
    exit;
} else {
    // что-то пошло не
    echo json_encode(['success' => false, 'errors' => ['database' => 'Ошибка при обновлении данных']]);
    exit;
}
?>
