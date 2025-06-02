<?php
function validateFullName($fullName) {
    if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ\s]+$/u", $fullName)) return "В ФИО должны быть только буквы и пробелы!";

    return null;
}

function validatePhone($phone) {
    if (!preg_match("/^\d{10,15}$/", $phone)) return "Телефон должен иметь от 10-ти до 15-ти цифр";

    return null;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Неверный email!";

    return null;
}

function validateBirthDate($birthDate) {
    if (empty($birthDate)) return "Дата рождения обязательна!";

    return null;
}

function validateGender($gender) {
    if (!in_array($gender, ['male', 'female'])) return "Укажите пол!";

    return null;
}

function validateLanguages($languages, $conn) {
    if (empty($languages)) return "Выберите хотя бы один язык!";

    $valid_languages = [];
    $stmt = $conn->prepare("SELECT id FROM programming_languages WHERE id = ?");
    foreach ($languages as $lang_id) {
        $stmt->bind_param("i", $lang_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $valid_languages[] = $lang_id;
        }
    }
    $stmt->close();
    if (empty($valid_languages)) return "Выбранные языки недействительны!";

    return null;
}

function validateBiography($biography) {
    if (empty($biography)) return "Биография обязательна!";

    return null;
}

function validateContract($contractAccepted) {
    if (!$contractAccepted) return "Согласитесь с контрактом!";

    return null;
}

function setError($field, $message) {
    $_SESSION['errors'][$field] = $message;
}

function saveToCookies($field, $value) {
    setcookie($field, $value, time() + 3600 * 24 * 365, "/");
}
