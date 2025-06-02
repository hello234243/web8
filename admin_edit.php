<?php
session_start();
$conn = require_once __DIR__ . '/config/database.php';
require_once 'utils/validation.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$applicant_id = $_GET['id'] ?? 0;
$errors = [];

if (!$applicant_id) {
    header('Location: admin.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM applicants WHERE id = ?");
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$applicant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$applicant) {
    header('Location: admin.php');
    exit;
}

$stmt = $conn->prepare("SELECT language_id FROM applicant_languages WHERE applicant_id = ?");
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$result = $stmt->get_result();
$current_languages = [];
while ($row = $result->fetch_assoc()) {
    $current_languages[] = $row['language_id'];
}
$stmt->close();

$languages = [];
$result = $conn->query("SELECT id, name FROM programming_languages");
while ($row = $result->fetch_assoc()) {
    $languages[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $new_languages = $_POST['languages'] ?? [];
    $biography = trim($_POST['biography'] ?? '');
    $contractAccepted = isset($_POST['contractAccepted']);

    if ($error = validateFullName($fullName)) {
        $errors['full_name'] = $error;
    }
    if ($error = validatePhone($phone)) {
        $errors['phone'] = $error;
    }
    if ($error = validateEmail($email)) {
        $errors['email'] = $error;
    }
    if ($error = validateBirthDate($birthDate)) {
        $errors['birth_date'] = $error;
    }
    if ($error = validateGender($gender)) {
        $errors['gender'] = $error;
    }
    if ($error = validateLanguages($new_languages, $conn)) {
        $errors['languages'] = $error;
    }
    if ($error = validateBiography($biography)) {
        $errors['biography'] = $error;
    }
    if ($error = validateContract($contractAccepted)) {
        $errors['contractAccepted'] = $error;
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE applicants SET full_name = ?, phone = ?, email = ?, birth_date = ?, gender = ?, biography = ?, contract_accepted = ? WHERE id = ?");
        $contractAcceptedInt = $contractAccepted ? 1 : 0;
        $stmt->bind_param("ssssssii", $fullName, $phone, $email, $birthDate, $gender, $biography, $contractAcceptedInt, $applicant_id);
        $stmt->execute();
        $stmt->close();

        $conn->query("DELETE FROM applicant_languages WHERE applicant_id = $applicant_id");
        $lang_stmt = $conn->prepare("INSERT INTO applicant_languages (applicant_id, language_id) VALUES (?, ?)");
        $lang_stmt->bind_param("ii", $applicant_id, $language_id);
        foreach ($new_languages as $language_id) {
            $lang_stmt->execute();
        }
        $lang_stmt->close();

        $conn->close();
        header('Location: admin.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать пользователя</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 600px;
            margin: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        h1 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #ffffff;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 8px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        select[multiple] {
            height: 120px;
        }
        input::placeholder, textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .error {
            color: #ff6b6b;
            font-size: 14px;
            margin-bottom: 10px;
        }
        button {
            padding: 12px 24px;
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: 500;
        }
        button:hover {
            background: linear-gradient(45deg, #45a049, #4CAF50);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        a {
            display: block;
            margin-top: 15px;
            color: #ffffff;
            text-decoration: none;
            text-align: center;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #4CAF50;
        }
        input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактировать пользователя</h1>
        <form method="POST">
            <label>ФИО</label>
            <label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($applicant['full_name']); ?>" placeholder="Введите ФИО">
            </label>
            <?php if (isset($errors['full_name'])): ?>
                <p class="error"><?php echo $errors['full_name']; ?></p>
            <?php endif; ?>

            <label>Телефон</label>
            <label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($applicant['phone']); ?>" placeholder="Введите номер телефона">
            </label>
            <?php if (isset($errors['phone'])): ?>
                <p class="error"><?php echo $errors['phone']; ?></p>
            <?php endif; ?>

            <label>Email</label>
            <label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($applicant['email']); ?>" placeholder="Введите email">
            </label>
            <?php if (isset($errors['email'])): ?>
                <p class="error"><?php echo $errors['email']; ?></p>
            <?php endif; ?>

            <label>Дата рождения</label>
            <label>
                <input type="date" name="birth_date" value="<?php echo htmlspecialchars($applicant['birth_date']); ?>">
            </label>
            <?php if (isset($errors['birth_date'])): ?>
                <p class="error"><?php echo $errors['birth_date']; ?></p>
            <?php endif; ?>

            <label>Пол</label>
            <label>
                <select name="gender">
                    <option value="male" <?php echo $applicant['gender'] === 'male' ? 'selected' : ''; ?>>Мужской</option>
                    <option value="female" <?php echo $applicant['gender'] === 'female' ? 'selected' : ''; ?>>Женский</option>
                </select>
            </label>
            <?php if (isset($errors['gender'])): ?>
                <p class="error"><?php echo $errors['gender']; ?></p>
            <?php endif; ?>

            <label>Языки программирования</label>
            <label>
                <select name="languages[]" multiple>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?php echo $lang['id']; ?>" <?php echo in_array($lang['id'], $current_languages) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lang['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php if (isset($errors['languages'])): ?>
                <p class="error"><?php echo $errors['languages']; ?></p>
            <?php endif; ?>

            <label>Биография</label>
            <label>
                <textarea name="biography" placeholder="Расскажите о себе"><?php echo htmlspecialchars($applicant['biography']); ?></textarea>
            </label>
            <?php if (isset($errors['biography'])): ?>
                <p class="error"><?php echo $errors['biography']; ?></p>
            <?php endif; ?>

            <label class="checkbox-label">
                <input type="checkbox" name="contractAccepted" <?php echo $applicant['contract_accepted'] ? 'checked' : ''; ?>>
                Согласен с контрактом
            </label>
            <?php if (isset($errors['contractAccepted'])): ?>
                <p class="error"><?php echo $errors['contractAccepted']; ?></p>
            <?php endif; ?>

            <button type="submit">Сохранить</button>
            <a href="admin.php">Назад</a>
        </form>
    </div>
</body>
</html>