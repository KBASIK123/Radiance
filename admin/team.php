<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    header("Location: /login.php");
    exit;
}

// Добавление/обновление члена команды
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $bio = trim($_POST['bio']);
    $facebook = trim($_POST['facebook']);
    $instagram = trim($_POST['instagram']);
    $twitter = trim($_POST['twitter']);
    $linkedin = trim($_POST['linkedin']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Обработка загрузки изображения
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/team/';
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image = $filename;
        }
    }
    
    if ($id) {
        // Обновление существующего члена команды
        if ($image) {
            // Удаляем старое изображение
            $stmt = $pdo->prepare("SELECT image_path FROM team WHERE id = ?");
            $stmt->execute([$id]);
            $oldImage = $stmt->fetchColumn();
            
            if ($oldImage && file_exists("../assets/img/team/$oldImage")) {
                unlink("../assets/img/team/$oldImage");
            }
            
            $stmt = $pdo->prepare("UPDATE team SET name = ?, position = ?, bio = ?, image_path = ?, facebook = ?, instagram = ?, twitter = ?, linkedin = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $position, $bio, $image, $facebook, $instagram, $twitter, $linkedin, $isActive, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE team SET name = ?, position = ?, bio = ?, facebook = ?, instagram = ?, twitter = ?, linkedin = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $position, $bio, $facebook, $instagram, $twitter, $linkedin, $isActive, $id]);
        }
        $success = "Член команды успешно обновлен";
    } else {
        // Добавление нового члена команды
        $stmt = $pdo->prepare("INSERT INTO team (name, position, bio, image_path, facebook, instagram, twitter, linkedin, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $position, $bio, $image, $facebook, $instagram, $twitter, $linkedin, $isActive]);
        $success = "Член команды успешно добавлен";
    }
}

// Удаление члена команды
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Получаем информацию о члене команды для удаления изображения
    $stmt = $pdo->prepare("SELECT image_path FROM team WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    
    if ($member && $member['image_path']) {
        $filePath = '../assets/img/team/' . $member['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Член команды успешно удален";
}

// Получение всех членов команды
$stmt = $pdo->query("SELECT * FROM team ORDER BY name");
$teamMembers = $stmt->fetchAll();

// Получение члена команды для редактирования
$editMember = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM team WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editMember = $stmt->fetch();
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/admin.css">

<section class="admin-section">
    <div class="admin-container">
        <div class="section-header">
            <h1 class="section-title">Управление командой</h1>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="admin-form">
            <h2><?= $editMember ? 'Редактировать члена команды' : 'Добавить члена команды' ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($editMember): ?>
                    <input type="hidden" name="id" value="<?= $editMember['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Имя:</label>
                    <input type="text" id="name" name="name" required value="<?= $editMember ? htmlspecialchars($editMember['name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="position">Должность:</label>
                    <input type="text" id="position" name="position" required value="<?= $editMember ? htmlspecialchars($editMember['position']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Биография:</label>
                    <textarea id="bio" name="bio" rows="4"><?= $editMember ? htmlspecialchars($editMember['bio']) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Фотография:</label>
                    <input type="file" id="image" name="image" <?= !$editMember ? 'required' : '' ?> accept="image/*">
                    <?php if ($editMember && $editMember['image_path']): ?>
                        <p>Текущее изображение: <?= htmlspecialchars($editMember['image_path']) ?></p>
                        <img src="/assets/img/team/<?= htmlspecialchars($editMember['image_path']) ?>" alt="<?= htmlspecialchars($editMember['name']) ?>" style="max-width: 200px;">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="facebook">Facebook:</label>
                    <input type="url" id="facebook" name="facebook" value="<?= $editMember ? htmlspecialchars($editMember['facebook']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="instagram">Instagram:</label>
                    <input type="url" id="instagram" name="instagram" value="<?= $editMember ? htmlspecialchars($editMember['instagram']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="twitter">Twitter:</label>
                    <input type="url" id="twitter" name="twitter" value="<?= $editMember ? htmlspecialchars($editMember['twitter']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="linkedin">LinkedIn:</label>
                    <input type="url" id="linkedin" name="linkedin" value="<?= $editMember ? htmlspecialchars($editMember['linkedin']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" <?= $editMember && $editMember['is_active'] ? 'checked' : '' ?>>
                        Активный сотрудник
                    </label>
                </div>
                
                <button type="submit" class="btn"><?= $editMember ? 'Обновить' : 'Добавить' ?></button>
                
                <?php if ($editMember): ?>
                    <a href="/admin/team.php" class="btn">Отмена</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="admin-card">
            <h2>Список команды</h2>
            
            <?php if (empty($teamMembers)): ?>
                <p>Нет добавленных членов команды</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Имя</th>
                            <th>Должность</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teamMembers as $member): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['name']) ?></td>
                                <td><?= htmlspecialchars($member['position']) ?></td>
                                <td><?= $member['is_active'] ? 'Активен' : 'Неактивен' ?></td>
                                <td>
                                    <a href="?edit=<?= $member['id'] ?>" class="btn">Редактировать</a>
                                    <a href="?delete=<?= $member['id'] ?>" class="btn delete-btn" onclick="return confirm('Удалить этого члена команды?')">Удалить</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>