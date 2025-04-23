<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: /profile.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Валидация
    if (empty($username)) $errors[] = "Имя пользователя обязательно";
    if (empty($email)) $errors[] = "Email обязателен";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Некорректный email";
    if (empty($password)) $errors[] = "Пароль обязателен";
    if ($password !== $confirm_password) $errors[] = "Пароли не совпадают";
    
    if (empty($errors)) {
        // Проверка существования пользователя
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetchColumn() == 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $userId = $pdo->lastInsertId();
                loginUser($userId, $username, 'user');
                header("Location: /profile.php");
                exit;
            } else {
                $errors[] = "Ошибка при регистрации";
            }
        } else {
            $errors[] = "Пользователь с таким именем или email уже существует";
        }
    }
}

include 'includes/header.php';
?>
<section class="auth-section">
    <div class="auth-container">
        <h2>Создать аккаунт</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" required 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Подтверждение пароля</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        </form>
        
        <p class="auth-link">Уже есть аккаунт? <a href="/login.php">Войти</a></p>
    </div>
</section>
<style>
    /* Общие стили для форм авторизации */
.auth-section {
    padding: 60px 0;
    background-color: #f8f9fa;
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
}

.auth-container {
    max-width: 450px;
    width: 100%;
    margin: 0 auto;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.auth-container h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #2c3e50;
    font-size: 1.8rem;
}

.auth-container p {
    text-align: center;
    margin-top: 20px;
    color: #7f8c8d;
}

.auth-container a {
    color: #3498db;
    text-decoration: none;
    transition: color 0.3s ease;
}

.auth-container a:hover {
    color: #2980b9;
    text-decoration: underline;
}

/* Стили для формы */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-group input:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Стили для кнопки */
.btn {
    display: block;
    width: 100%;
    padding: 12px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #2980b9;
}

/* Стили для алертов */
.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Адаптивность */
@media (max-width: 576px) {
    .auth-container {
        padding: 20px;
        margin: 0 15px;
    }
    
    .auth-section {
        padding: 30px 0;
    }
}
</style>
<?php include 'includes/footer.php'; ?>