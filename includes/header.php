<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Гостиница «Radiance»</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo" >«Radiance»</a>
                
                <!-- Десктопное меню -->
                <nav class="desktop-nav">
                    <ul>
                        <li><a href="/">Главная</a></li>
                        <li><a href="#about">О нас</a></li>
                        <li><a href="#rooms">Номера</a></li>
                        <li><a href="#facilities">Услуги</a></li>
                        <li><a href="#contact">Контакты</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/profile.php"><i class="fas fa-user-circle"></i> Личный кабинет</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a href="/admin/"><i class="fas fa-cog"></i> Админ-панель</a></li>
                            <?php endif; ?>
                            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
                        <?php else: ?>
                            <li><a href="/login.php"><i class="fas fa-sign-in-alt"></i> Вход</a></li>
                            <li><a href="/register.php"><i class="fas fa-user-plus"></i> Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                
                <!-- Кнопка бургер-меню для мобильных -->
                <button class="mobile-menu-toggle" aria-label="Меню">
                    <span class="toggle-line"></span>
                    <span class="toggle-line"></span>
                    <span class="toggle-line"></span>
                </button>
            </div>
        </div>
        
        <!-- Мобильное меню -->
        <nav class="mobile-nav">
            <ul>
                <li><a href="/">Главная</a></li>
                <li><a href="#about">О нас</a></li>
                <li><a href="#rooms">Номера</a></li>
                <li><a href="#facilities">Услуги</a></li>
                <li><a href="#contact">Контакты</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/profile.php"><i class="fas fa-user-circle"></i> Личный кабинет</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="/admin/"><i class="fas fa-cog"></i> Админ-панель</a></li>
                    <?php endif; ?>
                    <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
                <?php else: ?>
                    <li><a href="/login.php"><i class="fas fa-sign-in-alt"></i> Вход</a></li>
                    <li><a href="/register.php"><i class="fas fa-user-plus"></i> Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>

    <style>
    /* Стили для адаптивного хедера */
    .site-header {
        color: white;
        padding: 15px 0;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo {
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        text-decoration: none !important;
        font-family: 'Playfair Display', serif;
    }
    
    /* Десктопное меню */
    .desktop-nav ul {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .desktop-nav li {
        margin-left: 25px;
    }
    
    .desktop-nav a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .desktop-nav a:hover {
        color: white;
        
    }
    
    /* Мобильное меню */
    .mobile-menu-toggle {
        display: none;
        background: none;
        border: none;
        width: 30px;
        height: 24px;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        padding: 0;
    }
    
    .toggle-line {
        display: block;
        width: 100%;
        height: 3px;
        background: white;
        transition: all 0.3s ease;
        border-radius: 2px;
    }
    
    .mobile-nav {
        display: none;
        background: #2c3e50;
        padding: 20px;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    .mobile-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .mobile-nav li {
        margin-bottom: 15px;
    }
    
    .mobile-nav a {
        color: white;
        text-decoration: none;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
    }
    
    /* Анимация бургер-меню */
    .mobile-menu-toggle.active .toggle-line:nth-child(1) {
        transform: translateY(10px) rotate(45deg);
    }
    
    .mobile-menu-toggle.active .toggle-line:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-toggle.active .toggle-line:nth-child(3) {
        transform: translateY(-10px) rotate(-45deg);
    }
    
    .mobile-nav.active {
        display: block;
    }
    
    /* Адаптивные стили */
    @media (max-width: 992px) {
        .desktop-nav {
            display: none;
        }
        
        .mobile-menu-toggle {
            display: flex;
        }
    }
    
    @media (min-width: 993px) {
        .mobile-nav {
            display: none !important;
        }
    }
    
    </style>
    
    <script>
    // Активация мобильного меню
    document.querySelector('.mobile-menu-toggle').addEventListener('click', function() {
        this.classList.toggle('active');
        document.querySelector('.mobile-nav').classList.toggle('active');
    });
    
    // Закрытие меню при клике на ссылку
    document.querySelectorAll('.mobile-nav a').forEach(link => {
        link.addEventListener('click', () => {
            document.querySelector('.mobile-menu-toggle').classList.remove('active');
            document.querySelector('.mobile-nav').classList.remove('active');
        });
    });
    </script>