<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Добавляем функцию для преобразования статуса
function getStatusText($status) {
    $statuses = [
        'pending' => 'Ожидает подтверждения',
        'confirmed' => 'Подтверждено',
        'rejected' => 'Отклонено',
        'cancelled' => 'Отменено'
    ];
    return $statuses[$status] ?? $status;
}

if (!isLoggedIn()) {
    header("Location: /login.php");
    exit;
}

$user = getCurrentUser($pdo);

// Получаем бронирования пользователя
$stmt = $pdo->prepare("
    SELECT b.*, r.name as room_name, r.price as room_price, r.image as room_image 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Личный кабинет</h1>
            <p class="section-subtitle">Управление вашими бронированиями</p>
        </div>
        
        <div class="admin-card">
            <div class="profile-info">
                <div class="profile-header">
                    <h2>Добро пожаловать, <?= htmlspecialchars($user['username']) ?>!</h2>
                    <div class="profile-meta">
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Зарегистрирован: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bookings-list">
                <h2 class="section-subtitle">Ваши бронирования</h2>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>У вас пока нет бронирований</p>
                        <a href="/booking.php" class="btn btn-primary">Забронировать номер</a>
                    </div>
                <?php else: ?>
                    <div class="booking-grid">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-image">
                                    <img src="/assets/img/rooms/<?= htmlspecialchars($booking['room_image']) ?>" alt="<?= htmlspecialchars($booking['room_name']) ?>">
                                </div>
                                <div class="booking-details">
                                    <h3><?= htmlspecialchars($booking['room_name']) ?></h3>
                                    <div class="booking-meta">
                                        <p><i class="fas fa-calendar-check"></i> Заезд: <?= date('d.m.Y', strtotime($booking['check_in'])) ?></p>
                                        <p><i class="fas fa-calendar-times"></i> Выезд: <?= date('d.m.Y', strtotime($booking['check_out'])) ?></p>
                                        <p><i class="fas fa-user-friends"></i> Гостей: <?= $booking['guests'] ?></p>
                                        <p><i class="fas fa-ruble-sign"></i> Стоимость: <?= number_format($booking['total_price'], 0, '', ' ') ?> ₽</p>
                                    </div>
                                    <div class="booking-status">
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?= getStatusText($booking['status']) ?>
                                        </span>
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <small>Ожидает подтверждения администратором</small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($booking['special_requests'])): ?>
                                        <div class="booking-notes">
                                            <p><strong>Ваши пожелания:</strong></p>
                                            <p><?= htmlspecialchars($booking['special_requests']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>