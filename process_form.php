<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/home/b/b918347x/public_html/php_errors.log');

session_start();
ob_start();
require_once 'config/database.php';
require_once 'utils/validation.php';

error_log("Starting process_form.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['errors'] = [];
    $isValid = true;

    // Получение данных
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $languages = $_POST['languages'] ?? [];
    $biography = trim($_POST['biography'] ?? '');
    $contractAccepted = isset($_POST['contractAccepted']);

    error_log("Form data: full_name=$fullName, phone=$phone, email=$email, languages=" . print_r($languages, true));

    // Валидация
    if ($error = validateFullName($fullName)) {
        setError('full_name', $error);
        $isValid = false;
    } else {
        saveToCookies('full_name', $fullName);
    }

    if ($error = validatePhone($phone)) {
        setError('phone', $error);
        $isValid = false;
    } else {
        saveToCookies('phone', $phone);
    }

    if ($error = validateEmail($email)) {
        setError('email', $error);
        $isValid = false;
    } else {
        saveToCookies('email', $email);
    }

    if ($error = validateBirthDate($birthDate)) {
        setError('birth_date', $error);
        $isValid = false;
    } else {
        saveToCookies('birth_date', $birthDate);
    }

    if ($error = validateGender($gender)) {
        setError('gender', $error);
        $isValid = false;
    } else {
        saveToCookies('gender', $gender);
    }

    if ($error = validateLanguages($languages, $conn)) {
        setError('languages', $error);
        $isValid = false;
    } else {
        saveToCookies('languages', implode(',', $languages));
    }

    if ($error = validateBiography($biography)) {
        setError('biography', $error);
        $isValid = false;
    } else {
        saveToCookies('biography', $biography);
    }

    if ($error = validateContract($contractAccepted)) {
        setError('contractAccepted', $error);
        $isValid = false;
    }

    if (!$isValid) {
        error_log("Validation failed: " . print_r($_SESSION['errors'], true));
        header('Location: index.php');
        exit;
    }

    // Генерация логина и пароля
    $base_login = 'user_' . bin2hex(random_bytes(4));
    $login = $base_login;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM applicants WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_row()[0] == 0) break;
        $login = $base_login . '_' . $counter++;
        $stmt->close();
    }

    $password = bin2hex(random_bytes(8));
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    error_log("Generated login: $login, password: $password");

    // Сохранение в БД
    $stmt = $conn->prepare("INSERT INTO applicants (full_name, phone, email, birth_date, gender, biography, contract_accepted, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $contractAcceptedInt = $contractAccepted ? 1 : 0;
    $stmt->bind_param("ssssssiss", $fullName, $phone, $email, $birthDate, $gender, $biography, $contractAcceptedInt, $login, $password_hash);

    if ($stmt->execute()) {
        $applicant_id = $stmt->insert_id;

        // Вставка языков
        $lang_stmt = $conn->prepare("INSERT INTO applicant_languages (applicant_id, language_id) VALUES (?, ?)");
        $lang_stmt->bind_param("ii", $applicant_id, $language_id);

        foreach ($languages as $language_id) {
            if (!$lang_stmt->execute()) {
                error_log("Failed to insert language_id $language_id: " . $lang_stmt->error);
            }
        }

        $lang_stmt->close();
        $stmt->close();
        $conn->close();

        $_SESSION['success'] = true;
        $_SESSION['generated_login'] = $login;
        $_SESSION['generated_password'] = $password;

        error_log("Redirecting to success.php");
        header('Location: success.php');
        exit;
    } else {
        error_log("Database error: " . $stmt->error);
        echo "Database error: " . $stmt->error;
        exit;
    }
} else {
    error_log("Non-POST request, redirecting to index.php");
    header('Location: index.php');
    exit;
}
ob_end_flush();
?>