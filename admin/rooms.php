<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    // Валидация данных
    $data = [
        'name' => htmlspecialchars(trim($_POST['name'])),
        'description' => htmlspecialchars(trim($_POST['description'])),
        'price' => (float)$_POST['price'],
        'capacity' => (int)$_POST['capacity'],
        'size' => htmlspecialchars(trim($_POST['size'])),
        'amenities' => htmlspecialchars(trim($_POST['amenities'])),
        'is_available' => isset($_POST['is_available']) ? 1 : 0
    ];

    // Обработка изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/rooms/';
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;
        
        // Проверка типа файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Допустимы только изображения в формате JPG, PNG или GIF";
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $data['image'] = $filename;
        }
    }

    if (!isset($error)) {
        if ($id) {
            // Обновление номера
            if (isset($data['image'])) {
                // Удаляем старое изображение
                $stmt = $pdo->prepare("SELECT image FROM rooms WHERE id = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();
                
                if ($oldImage) {
                    $oldPath = $uploadDir . $oldImage;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE rooms SET name=?, description=?, price=?, capacity=?, size=?, amenities=?, image=?, is_available=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$data['name'], $data['description'], $data['price'], $data['capacity'], $data['size'], $data['amenities'], $data['image'], $data['is_available'], $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE rooms SET name=?, description=?, price=?, capacity=?, size=?, amenities=?, is_available=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$data['name'], $data['description'], $data['price'], $data['capacity'], $data['size'], $data['amenities'], $data['is_available'], $id]);
            }
            $success = "Номер успешно обновлен";
        } else {
            // Добавление нового номера
            $stmt = $pdo->prepare("INSERT INTO rooms (name, description, price, capacity, size, amenities, image, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['description'], $data['price'], $data['capacity'], $data['size'], $data['amenities'], $data['image'] ?? null, $data['is_available']]);
            $success = "Номер успешно добавлен";
        }
    }
}

// Удаление номера
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Получаем информацию о номере
    $stmt = $pdo->prepare("SELECT image FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();
    
    // Удаляем изображение
    if ($image) {
        $filePath = '../assets/img/rooms/' . $image;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    // Удаляем номер из БД
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Номер успешно удален";
}

// Получение всех номеров
$rooms = getAllRooms($pdo, false);

// Получение номера для редактирования
$editRoom = null;
if (isset($_GET['edit'])) {
    $editRoom = getRoomById($pdo, (int)$_GET['edit']);
}

include '../includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Управление номерами</h1>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="admin-form">
            <h2><?= $editRoom ? 'Редактировать номер' : 'Добавить номер' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editRoom): ?>
                    <input type="hidden" name="id" value="<?= $editRoom['id'] ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Название:</label>
                        <input type="text" id="name" name="name" required value="<?= $editRoom ? htmlspecialchars($editRoom['name']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Цена (₽):</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" required value="<?= $editRoom ? $editRoom['price'] : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" rows="4" required><?= $editRoom ? htmlspecialchars($editRoom['description']) : '' ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacity">Вместимость:</label>
                        <input type="number" id="capacity" name="capacity" min="1" required value="<?= $editRoom ? $editRoom['capacity'] : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Размер (м²):</label>
                        <input type="text" id="size" name="size" value="<?= $editRoom ? htmlspecialchars($editRoom['size']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="amenities">Удобства:</label>
                    <input type="text" id="amenities" name="amenities" value="<?= $editRoom ? htmlspecialchars($editRoom['amenities']) : '' ?>">
                    <small>Перечислите через запятую</small>
                </div>
                
                <div class="form-group">
                    <label for="image">Изображение:</label>
                    <input type="file" id="image" name="image" <?= !$editRoom ? 'required' : '' ?> accept="image/*">
                    <?php if ($editRoom && $editRoom['image']): ?>
                        <div class="current-image">
                            <p>Текущее изображение:</p>
                            <img src="/assets/img/rooms/<?= htmlspecialchars($editRoom['image']) ?>" alt="<?= htmlspecialchars($editRoom['name']) ?>">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_available" <?= $editRoom && $editRoom['is_available'] ? 'checked' : '' ?>>
                        <span>Доступен для бронирования</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= $editRoom ? 'Обновить' : 'Добавить' ?></button>
                    <a href="rooms.php" class="btn">Отмена</a>
                </div>
            </form>
        </div>
        <div class="admin-card">
            <h2>Список номеров</h2>
            
            <?php if (empty($rooms)): ?>
                <p>Нет добавленных номеров</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Цена</th>
                                <th>Вместимость</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?= $room['id'] ?></td>
                                    <td><?= htmlspecialchars($room['name']) ?></td>
                                    <td><?= number_format($room['price'], 0, '', ' ') ?> ₽</td>
                                    <td><?= $room['capacity'] ?></td>
                                    <td>
                                        <span class="status-badge <?= $room['is_available'] ? 'status-available' : 'status-unavailable' ?>">
                                            <?= $room['is_available'] ? 'Доступен' : 'Не доступен' ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="?edit=<?= $room['id'] ?>" class="btn btn-sm">Изменить</a>
                                        <a href="?delete=<?= $room['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить этот номер?')">Удалить</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>