<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Добавление/обновление изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $category = $_POST['category'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if ($id) {
        // Редактирование существующей записи
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../assets/img/gallery/';
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Допустимы только изображения в формате JPG, PNG или GIF";
            } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Удаляем старое изображение
                $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
                $stmt->execute([$id]);
                $oldImage = $stmt->fetch();
                if ($oldImage && file_exists($uploadDir . $oldImage['image_path'])) {
                    unlink($uploadDir . $oldImage['image_path']);
                }
                
                $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, image_path = ?, category = ?, is_featured = ? WHERE id = ?");
                $stmt->execute([$title, $description, $filename, $category, $is_featured, $id]);
            } else {
                $error = "Ошибка при загрузке изображения";
            }
        } else {
            // Обновляем без изменения изображения
            $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, category = ?, is_featured = ? WHERE id = ?");
            $stmt->execute([$title, $description, $category, $is_featured, $id]);
        }
        $success = "Изображение успешно обновлено";
    } else {
        // Добавление нового изображения
        if (isset($_FILES['image'])) {
            $uploadDir = '../assets/img/gallery/';
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $filename;
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                $error = "Допустимы только изображения в формате JPG, PNG или GIF";
            } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image_path, category, is_featured) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $filename, $category, $is_featured]);
                $success = "Изображение успешно загружено";
            } else {
                $error = "Ошибка при загрузке изображения";
            }
        } else {
            $error = "Необходимо выбрать изображение";
        }
    }
}

// Удаление изображения
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    
    if ($image) {
        $filePath = '../assets/img/gallery/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Изображение удалено";
    }
}

// Получение всех изображений
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC");
$images = $stmt->fetchAll();

// Получение данных для редактирования
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Управление галереей</h1>
            <p class="section-subtitle">Добавляйте и редактируйте изображения для галереи</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="admin-card">
            <h2><?= $editData ? 'Редактировать изображение' : 'Добавить изображение' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Название</label>
                        <input type="text" id="title" name="title" 
                               value="<?= $editData ? htmlspecialchars($editData['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Категория</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="hotel" <?= $editData && $editData['category'] === 'hotel' ? 'selected' : '' ?>>Гостиница</option>
                            <option value="rooms" <?= $editData && $editData['category'] === 'rooms' ? 'selected' : '' ?>>Номера</option>
                            <option value="restaurant" <?= $editData && $editData['category'] === 'restaurant' ? 'selected' : '' ?>>Ресторан</option>
                            <option value="spa" <?= $editData && $editData['category'] === 'spa' ? 'selected' : '' ?>>Спа</option>
                            <option value="pool" <?= $editData && $editData['category'] === 'pool' ? 'selected' : '' ?>>Бассейн</option>
                            <option value="other" <?= $editData && $editData['category'] === 'other' ? 'selected' : '' ?>>Другое</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= $editData ? htmlspecialchars($editData['description']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Изображение</label>
                    <input type="file" id="image" name="image" <?= !$editData ? 'required' : '' ?> accept="image/*">
                    <?php if ($editData): ?>
                        <div class="current-image">
                            <p>Текущее изображение:</p>
                            <img src="/assets/img/gallery/<?= htmlspecialchars($editData['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($editData['title']) ?>">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="is_featured" name="is_featured" <?= $editData && $editData['is_featured'] ? 'checked' : '' ?>>
                        <span>Рекомендуемое изображение</span>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="gallery.php" class="btn btn-outline">Отмена</a>
                </div>
            </form>
        </div>
        
        <div class="admin-card">
            <h2>Текущие изображения</h2>
            
            <?php if (empty($images)): ?>
                <p class="text-muted">Нет загруженных изображений</p>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($images as $image): ?>
                        <div class="gallery-item">
                            <div class="gallery-image">
                                <img src="/assets/img/gallery/<?= htmlspecialchars($image['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($image['title']) ?>">
                                <?php if ($image['is_featured']): ?>
                                    <span class="badge badge-primary">Рекомендуемое</span>
                                <?php endif; ?>
                            </div>
                            <div class="gallery-content">
                                <h3><?= htmlspecialchars($image['title']) ?></h3>
                                <p><?= htmlspecialchars($image['description']) ?></p>
                                <div class="gallery-meta">
                                    <span class="badge badge-secondary"><?= htmlspecialchars($image['category']) ?></span>
                                    <small><?= date('d.m.Y', strtotime($image['created_at'])) ?></small>
                                </div>
                                <div class="gallery-actions">
                                    <a href="?edit=<?= $image['id'] ?>" class="btn btn-sm btn-outline">Изменить</a>
                                    <a href="?delete=<?= $image['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Удалить это изображение?')">Удалить</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>