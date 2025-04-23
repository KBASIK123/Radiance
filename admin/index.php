<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Получаем бронирования, ожидающие подтверждения
$pendingBookings = [];
$stmt = $pdo->query("
    SELECT b.*, r.name as room_name, u.username as user_name 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    WHERE b.status = 'pending'
    ORDER BY b.created_at DESC
    LIMIT 5
");
$pendingBookings = $stmt->fetchAll();

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Админ-панель</h1>
            <p class="section-subtitle">Управление контентом сайта</p>
        </div>
        
        <?php if (!empty($pendingBookings)): ?>
            <div class="admin-card">
                <h2>Бронирования, ожидающие подтверждения</h2>
                <div class="pending-bookings">
                    <?php foreach ($pendingBookings as $booking): ?>
                        <div class="booking-item">
                            <div class="booking-info">
                                <h3><?= htmlspecialchars($booking['room_name']) ?></h3>
                                <p>Клиент: <?= htmlspecialchars($booking['user_name']) ?></p>
                                <p>Даты: <?= date('d.m.Y', strtotime($booking['check_in'])) ?> - <?= date('d.m.Y', strtotime($booking['check_out'])) ?></p>
                                <p>Гостей: <?= $booking['guests'] ?></p>
                            </div>
                            <div class="booking-actions">
                                <a href="/admin/approve_booking.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-success">Подтвердить</a>
                                <a href="/admin/reject_booking.php?id=<?= $booking['id'] ?>" class="btn btn-sm btn-danger">Отклонить</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="view-all">
                        <a href="/admin/bookings.php" class="btn btn-outline">Все бронирования</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="admin-grid">
            <!-- Остальные карточки админ-панели -->
            <a href="/admin/gallery.php" class="admin-card">
                <div class="card-icon">
                    <i class="fas fa-images"></i>
                </div>
                <h3>Управление галереей</h3>
                <p>Добавление и редактирование фотографий</p>
            </a>
            
            <a href="/admin/rooms.php" class="admin-card">
                <div class="card-icon">
                    <i class="fas fa-bed"></i>
                </div>
                <h3>Управление номерами</h3>
                <p>Редактирование номеров и цен</p>
            </a>
            
            <a href="/admin/bookings.php" class="admin-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Управление бронированиями</h3>
                <p>Просмотр и подтверждение броней</p>
            </a>
            
            <a href="/admin/team.php" class="admin-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Управление командой</h3>
                <p>Добавление сотрудников</p>
            </a>
            
            <a href="/admin/settings.php" class="admin-card">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <h3>Настройки</h3>
                <p>Основные настройки сайта</p>
            </a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>