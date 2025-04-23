<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Функция для получения текстового описания статуса
function getStatusText($status) {
    $statusMap = [
        'pending' => 'Ожидает',
        'confirmed' => 'Подтверждено',
        'rejected' => 'Отклонено',
        'cancelled' => 'Отменено'
    ];
    return $statusMap[substr($status, 0, 9)] ?? $status; // Ограничиваем длину статуса
}

// Изменение статуса бронирования
if (isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = substr($_GET['action'], 0, 8); // Ограничиваем длину действия
    
    $statusMap = [
        'approve' => 'confirmed',
        'reject' => 'rejected',
        'cancel' => 'cancelled'
    ];
    
    if (array_key_exists($action, $statusMap)) {
        $status = $statusMap[$action];
        
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            $_SESSION['success'] = "Бронирование успешно " . 
                ($action === 'approve' ? 'подтверждено' : 
                 ($action === 'reject' ? 'отклонено' : 'отменено'));
            header("Location: bookings.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка при обновлении статуса: " . $e->getMessage();
            header("Location: bookings.php");
            exit;
        }
    }
}

// Получаем все бронирования
$stmt = $pdo->query("
    SELECT b.*, r.name as room_name, u.username as user_name 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Управление бронированиями</h1>
            <p class="section-subtitle">Подтверждение и управление бронями</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Номер</th>
                            <th>Клиент</th>
                            <th>Даты</th>
                            <th>Гостей</th>
                            <th>Стоимость</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['id'] ?></td>
                                <td><?= htmlspecialchars($booking['room_name']) ?></td>
                                <td><?= htmlspecialchars($booking['user_name']) ?></td>
                                <td>
                                    <?= date('d.m.Y', strtotime($booking['check_in'])) ?><br>
                                    <?= date('d.m.Y', strtotime($booking['check_out'])) ?>
                                </td>
                                <td><?= $booking['guests'] ?></td>
                                <td><?= number_format($booking['total_price'], 0, '', ' ') ?> ₽</td>
                                <td>
                                    <span class="status-badge status-<?= $booking['status'] ?>">
                                        <?= getStatusText($booking['status']) ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <?php if ($booking['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $booking['id'] ?>" class="btn btn-sm btn-success">Подтвердить</a>
                                        <a href="?action=reject&id=<?= $booking['id'] ?>" class="btn btn-sm btn-danger">Отклонить</a>
                                    <?php elseif ($booking['status'] === 'confirmed'): ?>
                                        <a href="?action=cancel&id=<?= $booking['id'] ?>" class="btn btn-sm btn-warning">Отменить</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>