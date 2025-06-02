<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = require_once __DIR__ . '/config/database.php';

// Функция для установки ошибок в сессию
function setError($field, $message) {
    $_SESSION['errors'][$field] = $message;
}

// Проверка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF-проверка
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности: CSRF-токен недействителен');
    }

    // Очистка сессии ошибок
    $_SESSION['errors'] = [];

    // Флаг для проверки успешности валидации
    $isValid = true;

    // Получаем данные из формы
    $fullName = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $birthDate = trim($_POST['birth_date']);
    $gender = $_POST['gender'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $biography = trim($_POST['biography']);
    $contractAccepted = isset($_POST['contractAccepted']);

    // Валидация ФИО
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $fullName)) {
        setError('full_name', 'ФИО должно содержать только буквы и пробелы');
        $isValid = false;
    }

    // Валидация телефона
    if (!preg_match("/^\d{10,15}$/", $phone)) {
        setError('phone', 'Телефон должен содержать только цифры (от 10 до 15 цифр)');
        $isValid = false;
    }

    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setError('email', 'Введите правильный e-mail');
        $isValid = false;
    }

    // Валидация даты рождения
    if (empty($birthDate)) {
        setError('birth_date', 'Дата рождения обязательна');
        $isValid = false;
    }

    // Валидация пола
    if (!in_array($gender, ['male', 'female'])) {
        setError('gender', 'Укажите пол');
        $isValid = false;
    }

    // Валидация языков
    if (empty($languages)) {
        setError('languages', 'Выберите хотя бы один язык программирования');
        $isValid = false;
    }

    // Валидация биографии
    if (empty($biography)) {
        setError('biography', 'Биография обязательна');
        $isValid = false;
    }

    // Проверка согласия с контрактом
    if (!$contractAccepted) {
        setError('contractAccepted', 'Необходимо согласиться с контрактом');
        $isValid = false;
    }

    // Если все данные валидны, обновляем базу данных
    if ($isValid) {
        // Обновление данных в таблице applicants
        $stmt = $conn->prepare("UPDATE applicants SET full_name = ?, phone = ?, email = ?, birth_date = ?, gender = ?, biography = ?, contract_accepted = ? WHERE id = ?");
        $contractAcceptedInt = $contractAccepted ? 1 : 0;
        $stmt->bind_param("ssssssii", $fullName, $phone, $email, $birthDate, $gender, $biography, $contractAcceptedInt, $_SESSION['user_id']);

        if ($stmt->execute()) {
            // Удаляем старые языки
            $stmt = $conn->prepare("DELETE FROM applicant_languages WHERE applicant_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();

            // Вставляем новые языки
            $stmt = $conn->prepare("INSERT INTO applicant_languages (applicant_id, language_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $_SESSION['user_id'], $language_id);

            foreach ($languages as $language_id) {
                $stmt->execute();
            }

            $stmt->close();
            $conn->close();
            header('Location: edit_form.php');
            exit();
        } else {
            setError('database', 'Ошибка при обновлении данных');
            $isValid = false;
        }
    }

    // Если есть ошибки, перенаправляем обратно на форму редактирования
    header('Location: edit_form.php');
    exit();
}
?>
