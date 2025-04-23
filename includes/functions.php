<?php
/**
 * Функции для работы с гостиницей Radiance
 */

/**
 * Хеширование пароля
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Проверка пароля
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Получение всех номеров
 */
function getAllRooms($pdo, $availableOnly = true) {
    $sql = "SELECT * FROM rooms";
    if ($availableOnly) {
        $sql .= " WHERE is_available = 1";
    }
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Получение номера по ID
 */
function getRoomById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Проверка доступности номера на указанные даты
 */
function isRoomAvailable($pdo, $roomId, $checkIn, $checkOut) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings 
        WHERE room_id = :room_id 
        AND status IN ('confirmed', 'pending')
        AND (
            (check_in <= :check_out AND check_out >= :check_in)
        )
    ");
    $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
    $stmt->bindValue(':check_in', $checkIn);
    $stmt->bindValue(':check_out', $checkOut);
    $stmt->execute();
    return $stmt->fetchColumn() == 0;
}

/**
 * Получение изображений галереи
 */
function getGalleryImages($pdo, $category = null, $limit = null) {
    $sql = "SELECT * FROM gallery";
    $params = [];
    
    if ($category) {
        $sql .= " WHERE category = :category";
        $params[':category'] = $category;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
        $params[':limit'] = (int)$limit;
    }
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $paramType = strpos($key, ':limit') !== false 
            ? PDO::PARAM_INT 
            : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $paramType);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Получение членов команды
 */
function getTeamMembers($pdo, $activeOnly = true) {
    $sql = "SELECT * FROM team";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Расчет стоимости бронирования
 */
function calculateBookingPrice($roomPrice, $checkIn, $checkOut) {
    $start = new DateTime($checkIn);
    $end = new DateTime($checkOut);
    $interval = $start->diff($end);
    return $roomPrice * $interval->days;
}

/**
 * Создание бронирования
 */
function createBooking($pdo, $userId, $roomId, $checkIn, $checkOut, $guests, $specialRequests = null) {
    $room = getRoomById($pdo, $roomId);
    if (!$room) {
        throw new Exception('Номер не найден');
    }
    
    $totalPrice = calculateBookingPrice($room['price'], $checkIn, $checkOut);
    
    $stmt = $pdo->prepare("
        INSERT INTO bookings 
        (user_id, room_id, check_in, check_out, guests, total_price, special_requests) 
        VALUES (:user_id, :room_id, :check_in, :check_out, :guests, :total_price, :special_requests)
    ");
    
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':room_id', $roomId, PDO::PARAM_INT);
    $stmt->bindValue(':check_in', $checkIn);
    $stmt->bindValue(':check_out', $checkOut);
    $stmt->bindValue(':guests', $guests, PDO::PARAM_INT);
    $stmt->bindValue(':total_price', $totalPrice);
    $stmt->bindValue(':special_requests', $specialRequests);
    
    return $stmt->execute();
}

/**
 * Получение бронирований пользователя
 */
function getUserBookings($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT b.*, r.name as room_name, r.price as room_price 
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.user_id = :user_id
        ORDER BY b.created_at DESC
    ");
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Проверка существования пользователя
 */
function userExists($pdo, $username, $email) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM users 
        WHERE username = :username OR email = :email
    ");
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

/**
 * Создание нового пользователя
 */
function createUser($pdo, $username, $email, $password, $fullName = null, $phone = null) {
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (username, email, password, full_name, phone) 
        VALUES (:username, :email, :password, :full_name, :phone)
    ");
    
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':password', hashPassword($password));
    $stmt->bindValue(':full_name', $fullName);
    $stmt->bindValue(':phone', $phone);
    
    return $stmt->execute() ? $pdo->lastInsertId() : false;
}

