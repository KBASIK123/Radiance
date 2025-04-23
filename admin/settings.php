<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Здесь можно добавить логику для сохранения настроек сайта
// Например, название отеля, контактные данные, настройки бронирования и т.д.

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Настройки сайта</h1>
        </div>
        
        <div class="admin-form">
            <div class="form-group">
                <label for="hotel_name">Название отеля:</label>
                <input type="text" id="hotel_name" name="hotel_name" value="Гостиница «Radiance»" required>
            </div>
            
            <div class="form-group">
                <label for="hotel_description">Описание отеля:</label>
                <textarea id="hotel_description" name="hotel_description" rows="4" required>Добро пожаловать в уютную и современную гостиницу, где каждый гость чувствует себя как дома.</textarea>
            </div>
            
            <div class="form-group">
                <label for="hotel_address">Адрес:</label>
                <input type="text" id="hotel_address" name="hotel_address" value="Москва, ул. Примерная, д. 15, корп. 3" required>
            </div>
            
            <div class="form-group">
                <label for="hotel_phone">Телефон:</label>
                <input type="tel" id="hotel_phone" name="hotel_phone" value="+7 (495) 123-45-67" required>
            </div>
            
            <div class="form-group">
                <label for="hotel_email">Email:</label>
                <input type="email" id="hotel_email" name="hotel_email" value="info@hotel-example.ru" required>
            </div>
            
            <div class="form-group">
                <label for="booking_enabled">Бронирование номеров:</label>
                <select id="booking_enabled" name="booking_enabled">
                    <option value="1" selected>Включено</option>
                    <option value="0">Выключено</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="maintenance_mode">Режим обслуживания:</label>
                <select id="maintenance_mode" name="maintenance_mode">
                    <option value="0" selected>Сайт работает</option>
                    <option value="1">Техническое обслуживание</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Сохранить настройки</button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>