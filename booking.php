<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: /login.php");
    exit;
}

$user = getCurrentUser($pdo);
$rooms = getAllRooms($pdo);
$error = '';
$success = '';

// Обработка формы бронирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int)$_POST['room_id'];
    $checkIn = $_POST['check_in'];
    $checkOut = $_POST['check_out'];
    $guests = (int)$_POST['guests'];
    $specialRequests = trim($_POST['special_requests']);
    
    // Проверка доступности номера
    if (isRoomAvailable($pdo, $roomId, $checkIn, $checkOut)) {
        $room = getRoomById($pdo, $roomId);
        $totalPrice = calculateBookingPrice($room['price'], $checkIn, $checkOut);
        
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, special_requests)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$user['id'], $roomId, $checkIn, $checkOut, $guests, $totalPrice, $specialRequests])) {
            $success = "Бронирование успешно оформлено! Номер брони: " . $pdo->lastInsertId();
        } else {
            $error = "Ошибка при бронировании. Пожалуйста, попробуйте позже.";
        }
    } else {
        $error = "Выбранный номер недоступен на указанные даты. Пожалуйста, выберите другие даты или номер.";
    }
}

include 'includes/header.php';
?>

<section class="booking-section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Бронирование номера</h1>
            <p class="section-subtitle">Забронируйте номер для незабываемого отдыха</p>
        </div>
        
        <?php if ($error) : ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="booking-content">
            <form method="POST" class="booking-form">
                <div class="form-group">
                    <label for="room_id">Выберите номер:</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">-- Выберите номер --</option>
                        <?php foreach ($rooms as $room): ?>
                            <option value="<?= $room['id'] ?>">
                                <?= htmlspecialchars($room['name']) ?> (<?= number_format($room['price'], 0, '', ' ') ?> ₽/ночь)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="check_in">Дата заезда:</label>
                        <input type="date" id="check_in" name="check_in" required min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="check_out">Дата выезда:</label>
                        <input type="date" id="check_out" name="check_out" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="guests">Количество гостей:</label>
                    <input type="number" id="guests" name="guests" min="1" max="4" required value="1">
                </div>
                
                <div class="form-group">
                    <label for="special_requests">Особые пожелания:</label>
                    <textarea id="special_requests" name="special_requests" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Забронировать</button>
            </form>
            
            <div class="rooms-preview">
                <h2 class="section-subtitle">Наши номера</h2>
                <div class="rooms-grid">
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-image">
                                <img src="/assets/img/rooms/<?= htmlspecialchars($room['image']) ?>" alt="<?= htmlspecialchars($room['name']) ?>">
                            </div>
                            <div class="room-body">
                                <h3><?= htmlspecialchars($room['name']) ?></h3>
                                <div class="room-meta">
                                    <span><i class="fas fa-user-friends"></i> <?= $room['capacity'] ?> чел.</span>
                                    <span><i class="fas fa-ruble-sign"></i> <?= number_format($room['price'], 0, '', ' ') ?> ₽/ночь</span>
                                </div>
                                <p><?= htmlspecialchars($room['description']) ?></p>
                                <div class="room-amenities">
                                    <h4>Удобства:</h4>
                                    <ul>
                                        <?php foreach (explode(',', $room['amenities']) as $amenity): ?>
                                            <li><?= trim($amenity) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
}

.form-row {
    display: flex;
    gap: 20px;
}

.form-row .form-group {
    flex: 1;
}
.room-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.room-card:hover {
    transform: translateY(-5px);
}

.room-image {
    height: 200px;
    overflow: hidden;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.room-card:hover .room-image img {
    transform: scale(1.05);
}

.room-body {
    padding: 20px;
}

.room-meta {
    display: flex;
    gap: 15px;
    margin: 10px 0;
    color: #7f8c8d;
    font-size: 14px;
}

.room-amenities ul {
    margin-top: 10px;
    padding-left: 20px;
}

.room-amenities li {
    margin-bottom: 5px;
    color: #7f8c8d;
}

.btn-primary {
    background: #3498db;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-primary:hover {
    background: #2980b9;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert.error {
    background: #f8d7da;
    color: #721c24;
}

.alert.success {
    background: #d4edda;
    color: #155724;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .booking-content {
        grid-template-columns: 1fr;
    }
}
.booking-section {
    padding: 80px 0;
    background-color: #f8f9fa;
    text-align: center; /* Центрируем текст в секции */
}

.booking-content {
    display: grid;
    grid-template-columns: 1fr;
    gap: 50px;
    max-width: 1200px; /* Ограничиваем максимальную ширину */
    margin: 0 auto; /* Центрируем содержимое */
    padding: 0 20px; /* Добавляем отступы по бокам */
}

.booking-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    text-align: left; /* Текст формы оставляем слева */
    margin: 0 auto; /* Центрируем форму */
    max-width: 800px; /* Ограничиваем ширину формы */
}

.section-header {
    max-width: 800px;
    margin: 0 auto 50px;
    text-align: center; /* Убедимся, что заголовки центрированы */
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 30px;
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
}
</style>

<script>
document.getElementById('check_in').addEventListener('change', function() {
    const checkIn = new Date(this.value);
    checkIn.setDate(checkIn.getDate() + 1);
    const minCheckOut = checkIn.toISOString().split('T')[0];
    document.getElementById('check_out').min = minCheckOut;
    
    if (document.getElementById('check_out').value < minCheckOut) {
        document.getElementById('check_out').value = minCheckOut;
    }
});
</script>

<?php include 'includes/footer.php'; ?>