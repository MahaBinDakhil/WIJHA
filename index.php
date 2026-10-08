<?php
/* =====================================================
   index.php — الصفحة الرئيسية (الجزء 1)
   سلايدر + نبذة + المدن + الجولات المميزة
   كل البيانات تنجاب من القاعدة (Read)
   ===================================================== */
require_once 'includes/db.php';

// ---------- السلايدر: الجولات المميزة (is_featured = 1) ----------
$slides = $conn->query("
    SELECT t.id, t.title, t.short_desc, t.image, c.name AS city_name
    FROM tours t JOIN cities c ON c.id = t.city_id
    WHERE t.is_featured = 1
    ORDER BY t.tour_date
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// إذا ما فيه جولات مميزة، ناخذ أول 5 جولات عشان السلايدر ما يطلع فاضي
if (!$slides) {
    $slides = $conn->query("
        SELECT t.id, t.title, t.short_desc, t.image, c.name AS city_name
        FROM tours t JOIN cities c ON c.id = t.city_id
        ORDER BY t.tour_date LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);
}

// ---------- المدن + عدد الجولات في كل مدينة ----------
$cities = $conn->query("
    SELECT c.id, c.name, c.description, c.image, COUNT(t.id) AS tours_count
    FROM cities c LEFT JOIN tours t ON t.city_id = c.id
    GROUP BY c.id
    ORDER BY tours_count DESC
")->fetch_all(MYSQLI_ASSOC);

// ---------- أقرب 3 جولات قادمة ----------
$upcoming = $conn->query("
    SELECT t.*, c.name AS city_name
    FROM tours t JOIN cities c ON c.id = t.city_id
    WHERE t.tour_date >= CURDATE() AND t.available_seats > 0
    ORDER BY t.tour_date
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

$pageTitle  = 'الرئيسية';
$activePage = 'home';
require_once 'includes/header.php';
?>

<!-- ========== 1) السلايدر ========== -->
<section class="hero-slider" id="heroSlider">
    <?php foreach ($slides as $i => $s): ?>
        <div class="slide <?= $i === 0 ? 'active' : '' ?>">
            <img src="images/<?= e($s['image']) ?>" alt="<?= e($s['title']) ?>">
            <div class="slide-content container">
                <span class="badge badge-sand">📍 <?= e($s['city_name']) ?></span>
                <h1><?= e($s['title']) ?></h1>
                <p><?= e($s['short_desc']) ?></p>
                <a href="tour.php?id=<?= (int)$s['id'] ?>" class="btn btn-light">احجز الآن</a>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (count($slides) > 1): ?>
        <button class="slider-btn prev" id="sliderPrev" aria-label="السابق">❯</button>
        <button class="slider-btn next" id="sliderNext" aria-label="التالي">❮</button>
        <div class="slider-dots" id="sliderDots">
            <?php foreach ($slides as $i => $s): ?>
                <button class="dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" aria-label="شريحة <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ========== 2) نبذة عن المنصة ========== -->
<section class="section">
    <div class="container">
        <div class="about-grid">
            <div>
                <span class="eyebrow">عن وِجهة</span>
                <h2>اكتشف المملكة بعيون جديدة</h2>
                <p class="muted">
                    وِجهة منصة تجمع لك أجمل الجولات السياحية في مدن المملكة — من آثار العلا النبطية،
                    إلى ضباب أبها، وشعاب البحر الأحمر. احجز جولتك بخطوات بسيطة مع مرشدين محليين.
                </p>
                <a href="tours.php" class="btn" style="margin-top:18px">تصفّح كل الجولات</a>
            </div>
            <div class="features">
                <div class="feature">
                    <span>🇸🇦</span>
                    <div><strong>رؤية 2030</strong><p class="muted">ندعم مستهدفات تنمية السياحة الوطنية</p></div>
                </div>
                <div class="feature">
                    <span>🧭</span>
                    <div><strong>مرشدون محليون</strong><p class="muted">يعرفون قصص المكان وتفاصيله</p></div>
                </div>
                <div class="feature">
                    <span>🎟️</span>
                    <div><strong>حجز سهل</strong><p class="muted">اختر جولتك واحجز في أقل من دقيقة</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== 3) المدن ========== -->
<section class="section section-sand">
    <div class="container">
        <h2 class="section-title text-center">وجهات المملكة</h2>
        <p class="section-subtitle text-center">اختر مدينتك وشوف جولاتها</p>

        <div class="cities-grid">
            <?php foreach ($cities as $c): ?>
                <a href="tours.php?city=<?= (int)$c['id'] ?>" class="city-card">
                    <img src="images/<?= e($c['image']) ?>" alt="<?= e($c['name']) ?>" loading="lazy">
                    <div class="city-info">
                        <h3><?= e($c['name']) ?></h3>
                        <span><?= (int)$c['tours_count'] ?> <?= $c['tours_count'] == 1 ? 'جولة' : 'جولات' ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ========== 4) الجولات القادمة ========== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <h2 class="section-title">جولات قادمة</h2>
                <p class="section-subtitle">لا تفوّت أقرب الرحلات</p>
            </div>
            <a href="tours.php" class="btn btn-outline">عرض الكل</a>
        </div>

        <div class="cards-grid">
            <?php foreach ($upcoming as $tour): ?>
                <article class="card">
                    <div class="card-img">
                        <img src="images/<?= e($tour['image']) ?>" alt="<?= e($tour['title']) ?>" loading="lazy">
                        <span class="badge"><?= e($tour['type']) ?></span>
                    </div>
                    <div class="card-body">
                        <h3><?= e($tour['title']) ?></h3>
                        <div class="card-meta">
                            <span>📍 <?= e($tour['city_name']) ?></span>
                            <span>📅 <?= arabic_date($tour['tour_date']) ?></span>
                            <span>⏱ <?= e($tour['duration']) ?></span>
                        </div>
                        <p class="muted"><?= e($tour['short_desc']) ?></p>
                    </div>
                    <div class="card-footer">
                        <span class="price"><?= price($tour['price']) ?></span>
                        <a href="tour.php?id=<?= (int)$tour['id'] ?>" class="btn btn-sm">التفاصيل</a>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (!$upcoming): ?>
                <p class="muted">لا توجد جولات قادمة حالياً.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
