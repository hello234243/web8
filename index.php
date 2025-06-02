<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Получаем ошибки из сессии
$errors = $_SESSION['errors'] ?? [];
// Очищаем ошибки после использования
$_SESSION['errors'] = [];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма регистрации</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="form-container">
        <h1>Регистрация</h1>

        <!-- Сообщения об ошибках -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $field => $error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="process_form.php" method="POST">
            <div class="form-group">
                <label for="fullName">ФИО:</label>
                <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($_COOKIE['full_name'] ?? ''); ?>" 
                    style="border-color: <?php echo isset($errors['full_name']) ? 'red' : '#ecf0f1'; ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_COOKIE['phone'] ?? ''); ?>" 
                    style="border-color: <?php echo isset($errors['phone']) ? 'red' : '#ecf0f1'; ?>" required pattern="\d{10,15}">
            </div>

            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_COOKIE['email'] ?? ''); ?>" 
                    style="border-color: <?php echo isset($errors['email']) ? 'red' : '#ecf0f1'; ?>" required>
            </div>

            <div class="form-group">
                <label for="birthDate">Дата рождения:</label>
                <input type="date" id="birthDate" name="birth_date" value="<?php echo htmlspecialchars($_COOKIE['birth_date'] ?? ''); ?>" 
                    style="border-color: <?php echo isset($errors['birth_date']) ? 'red' : '#ecf0f1'; ?>" required>
            </div>

            <div class="form-group">
                <label>Пол:</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?php echo ($_COOKIE['gender'] ?? '') === 'male' ? 'checked' : ''; ?>> Мужчина</label>
                    <label><input type="radio" name="gender" value="female" <?php echo ($_COOKIE['gender'] ?? '') === 'female' ? 'checked' : ''; ?>> Женщина</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['gender']); ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="languages">Любимый язык программирования:</label>
                <select name="languages[]" id="languages" multiple required 
                    style="border-color: <?php echo isset($errors['languages']) ? 'red' : '#ecf0f1'; ?>">
                    <?php
                    $saved_languages = explode(',', $_COOKIE['languages'] ?? '');
                    $languages = [
                        1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript', 5 => 'PHP',
                        6 => 'Python', 7 => 'Java', 8 => 'Haskell', 9 => 'Clojure', 10 => 'Prolog',
                        11 => 'Scala', 12 => 'Go'
                    ];
                    foreach ($languages as $value => $name):
                    ?>
                        <option value="<?php echo $value; ?>" <?php echo in_array((string)$value, $saved_languages) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['languages']); ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="biography">Биография:</label>
                <textarea id="biography" name="biography" required 
                    style="border-color: <?php echo isset($errors['biography']) ? 'red' : '#ecf0f1'; ?>">
                    <?php echo htmlspecialchars($_COOKIE['biography'] ?? ''); ?>
                </textarea>
            </div>

            <div class="form-group">
                <label for="contractAccepted">
                    <input type="checkbox" name="contractAccepted" id="contractAccepted" required> Я согласен с условиями контракта
                </label>
                <?php if (isset($errors['contractAccepted'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['contractAccepted']); ?></p>
                <?php endif; ?>
            </div>

            <button type="submit">Отправить</button>
        </form>
        <p><a href="login.php">Войти для редактирования данных</a></p>
    </div>
</body>
</html>