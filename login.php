<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: /profile.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        loginUser($user['id'], $user['username'], $user['role']);
        header("Location: /profile.php");
        exit;
    } else {
        $error = "Неверное имя пользователя или пароль";
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <h2>Вход в систему</h2>
        
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Имя пользователя</label>
                <input type="text" id="username" name="username" required
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Войти</button>
        </form>
        
        <p class="auth-link">Нет аккаунта? <a href="/register.php">Зарегистрироваться</a></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
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