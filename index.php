<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Получение данных
$featuredRooms = getAllRooms($pdo, true);
$galleryImages = getGalleryImages($pdo, null, 6);
$teamMembers = getTeamMembers($pdo);

// Проверка подключения к БД
try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- Герой-секция -->
<section class="hero-section">
    <div class="container">
        <h1 class="hero-title">Добро пожаловать в Radiance</h1>
        <p class="hero-subtitle">Роскошь и комфорт в самом сердце города</p>
        <a href="/booking.php" class="btn">Забронировать номер</a>
    </div>
</section>

<!-- О гостинице -->
<section  id="about" class="section about-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">О нашей гостинице</h2>
            <p class="section-subtitle">Уют и комфорт для вашего идеального отдыха</p>
        </div>
        
        <p>Гостиница Radiance расположена в живописном районе с прекрасным видом на город. Мы предлагаем комфортабельные номера различных категорий, оснащенные всем необходимым для вашего отдыха.</p>
        
        <ul class="features-list">
            <li><i class="fas fa-wifi"></i> Бесплатный Wi-Fi</li>
            <li><i class="fas fa-utensils"></i> Ресторан с панорамным видом</li>
            <li><i class="fas fa-swimming-pool"></i> Крытый бассейн с подогревом</li>
            <li><i class="fas fa-spa"></i> СПА-центр</li>
        </ul>
    </div>
</section>

<!-- Оптимизированный горизонтальный блок "Наши номера" -->
<section id="rooms" class="section rooms-section">
    <div class="container">
        <div class="section-header">
            <h2>Наши номера</h2>
            <p class="section-subtitle">Комфорт для вашего идеального отдыха</p>
        </div>
        
        <div class="horizontal-rooms-container">
            <div class="horizontal-rooms-scroll">
                <?php 
                // Получаем все номера из базы данных
                $rooms = getAllRooms($pdo, true);
                
                foreach ($rooms as $room): 
                ?>
                <div class="horizontal-room-card">
                    <div class="room-image-wrapper">
                        <img src="/assets/img/rooms/<?= htmlspecialchars($room['image']) ?>" 
                             alt="<?= htmlspecialchars($room['name']) ?>" 
                             class="room-image"
                             loading="lazy">
                        <div class="room-badge"><?= htmlspecialchars($room['name']) ?></div>
                    </div>
                    <div class="room-content">
                        <div class="room-meta">
                            <span><i class="fas fa-user-friends"></i> <?= $room['capacity'] ?> чел.</span>
                            <span><i class="fas fa-vector-square"></i> <?= $room['size'] ?> м²</span>
                        </div>
                        <p><?= htmlspecialchars($room['description']) ?></p>
                        <div class="room-amenities">
                            <h4>Удобства:</h4>
                            <ul>
                                <?php 
                                $amenities = explode(',', $room['amenities']);
                                foreach ($amenities as $amenity): 
                                    if (!empty(trim($amenity))):
                                ?>
                                <li><?= htmlspecialchars(trim($amenity)) ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span>от <?= number_format($room['price'], 0, '', ' ') ?> ₽</span>
                                <small>за ночь</small>
                            </div>
                            <a href="/booking.php?room=<?= $room['id'] ?>" class="btn btn-primary">Забронировать</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>


<!-- Галерея -->
<section id="gallery" class="section gallery-section">
    <div class="container">
        <div class="section-header">
            <h2>Галерея</h2>
            <p class="section-subtitle">Окунитесь в атмосферу нашего отеля</p>
        </div>
        
        <!-- Фильтры по категориям -->
        <div class="gallery-filters">
            <button class="filter-btn active" data-filter="all">Все</button>
            <button class="filter-btn" data-filter="hotel">Гостиница</button>
            <button class="filter-btn" data-filter="rooms">Номера</button>
            <button class="filter-btn" data-filter="restaurant">Ресторан</button>
            <button class="filter-btn" data-filter="spa">Спа</button>
            <button class="filter-btn" data-filter="pool">Бассейн</button>
        </div>
        
        <!-- Горизонтальная галерея с прокруткой -->
        <div class="horizontal-gallery-container">
            <div class="horizontal-gallery-scroll">
                <?php foreach ($galleryImages as $image): ?>
                <div class="gallery-item" data-category="<?= htmlspecialchars($image['category']) ?>">
                    <div class="gallery-image-wrapper">
                        <img src="/assets/img/gallery/<?= htmlspecialchars($image['image_path']) ?>" 
                             alt="<?= htmlspecialchars($image['title']) ?>" 
                             class="gallery-image"
                             loading="lazy">
                        <?php if ($image['is_featured']): ?>
                        <div class="featured-badge">Рекомендуем</div>
                        <?php endif; ?>
                        <div class="gallery-overlay">
                            <div class="overlay-content">
                                <h3><?= htmlspecialchars($image['title']) ?></h3>
                                <p><?= htmlspecialchars($image['description']) ?></p>
                                <span class="category-badge"><?= htmlspecialchars($image['category']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Кнопка для админа -->
        <?php if (isAdmin()): ?>
        <div class="text-center mt-4">
            <a href="/admin/gallery.php" class="btn btn-outline-primary">Управление галереей</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Услуги -->
<section  id="facilities" class="section bg-light">
    <div class="container">
        <div class="section-header">
            <h2>Наши услуги</h2>
            <p class="section-subtitle">Все для вашего комфорта</p>
        </div>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Ресторан</h3>
                <p>Наш ресторан предлагает блюда международной кухни, приготовленные из свежих местных продуктов.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-swimming-pool"></i>
                </div>
                <h3>Бассейн</h3>
                <p>Крытый бассейн с подогревом работает круглый год. Идеальное место для расслабления.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-spa"></i>
                </div>
                <h3>СПА-центр</h3>
                <p>Широкий выбор массажей и косметических процедур для полного расслабления.</p>
            </div>
            
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h3>Фитнес-зал</h3>
                <p>Современное оборудование для поддержания формы во время путешествия.</p>
            </div>
        </div>
    </div>
</section>

<!-- Команда -->
<section id="team" class="section">
    <div class="container">
        <h2>Наша команда</h2>
        <div class="team-grid">
            <?php foreach ($teamMembers as $member): ?>
                <div class="team-card">
                    <img src="/assets/img/team/<?= htmlspecialchars($member['image_path']) ?>" 
                         alt="<?= htmlspecialchars($member['name']) ?>" 
                         class="team-img">
                    <div class="team-info">
                        <h3><?= htmlspecialchars($member['name']) ?></h3>
                        <p class="position"><?= htmlspecialchars($member['position']) ?></p>
                        <p><?= htmlspecialchars($member['bio']) ?></p>
                        
                        <div class="social-links">
                            <?php if ($member['facebook']): ?>
                                <a href="<?= htmlspecialchars($member['facebook']) ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                            <?php endif; ?>
                            
                            <?php if ($member['instagram']): ?>
                                <a href="<?= htmlspecialchars($member['instagram']) ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                            <?php endif; ?>
                            
                            <?php if ($member['twitter']): ?>
                                <a href="<?= htmlspecialchars($member['twitter']) ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            
                            <?php if ($member['linkedin']): ?>
                                <a href="<?= htmlspecialchars($member['linkedin']) ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Отзывы -->
<section class="section reviews-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Отзывы гостей</h2>
            <p class="section-subtitle">Что говорят наши клиенты</p>
        </div>
        
        <div class="reviews-slider">
            <div class="review-card">
                <div class="review-rating">★ ★ ★ ★ ★</div>
                <p class="review-text">"Прекрасный отель с отличным сервисом. Номера чистые и уютные, персонал внимательный и дружелюбный."</p>
                <p class="review-author">- Анна, Москва</p>
            </div>
            
            <div class="review-card">
                <div class="review-rating">★ ★ ★ ★ ★</div>
                <p class="review-text">"Отличное расположение, вкусные завтраки и комфортные кровати. Обязательно вернемся снова!"</p>
                <p class="review-author">- Игорь, Санкт-Петербург</p>
            </div>
        </div>
    </div>
</section>

<!-- Контакты -->
<section  id="contact"class="section contacts-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Контакты</h2>
            <p class="section-subtitle">Мы всегда рады вам помочь</p>
        </div>
        
        <div class="contact-grid">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Адрес</h3>
                <p>Москва, ул. Примерная, д. 15</p>
            </div>
            
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h3>Телефон</h3>
                <p>+7 (495) 123-45-67</p>
            </div>
            
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>info@hotel-radiance.ru</p>
            </div>
            
            <div class="contact-item">
                <i class="fas fa-clock"></i>
                <h3>Режим работы</h3>
                <p>Круглосуточно, 24/7</p>
            </div>
        </div>
        
        <!-- Карта с отступом -->
        <div class="map-container" style="margin-top: 50px; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2245.373789929732!2d37.61763331593095!3d55.75199998055186!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46b54a5a738fa419%3A0x7c347d506f52311f!2z0JrRgNCw0YHQvdCw0Y8g0J_Qu9C-0YnQsNC00Yw!5e0!3m2!1sru!2sru!4v1620000000000!5m2!1sru!2sru" 
                    width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
<script>
// Фильтрация по категориям
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Удаляем активный класс у всех кнопок
            filterBtns.forEach(b => b.classList.remove('active'));
            // Добавляем активный класс текущей кнопке
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            galleryItems.forEach(item => {
                if (filter === 'all' || item.dataset.category === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>