<?php
/* =====================================================
   tours.php — معرض الجولات + الفلترة (الجزء 1)
   PHP يجيب كل الجولات من القاعدة (Read)
   JavaScript يفلترها مباشرة بدون إعادة تحميل الصفحة
   ===================================================== */
require_once 'includes/db.php';

// كل الجولات مع اسم المدينة
$tours = $conn->query("
    SELECT t.id, t.title, t.type, t.short_desc, t.image, t.tour_date,
           t.duration, t.price, t.available_seats, t.city_id,
           c.name AS city_name
    FROM tours t
    JOIN cities c ON c.id = t.city_id
    ORDER BY t.tour_date
")->fetch_all(MYSQLI_ASSOC);

// المدن لقائمة الفلترة
$cities = $conn->query("SELECT id, name FROM cities ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// الأنواع (نفس قيم ENUM في القاعدة)
$types = ['تاريخية', 'طبيعة', 'مغامرات', 'ثقافية'];

// إذا جاية من الرئيسية بالضغط على مدينة: tours.php?city=3
$selectedCity = isset($_GET['city']) ? (int)$_GET['city'] : 0;

$pageTitle  = 'الجولات';
$activePage = 'tours';
require_once 'includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>الجولات</h1>
        <p>اختر جولتك القادمة من بين <?= count($tours) ?> تجربة في مدن المملكة</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- ========== أدوات الفلترة ========== -->
        <div class="filters" id="filters">
            <div class="filter-search">
                <input type="search" id="searchInput" class="form-control" placeholder="🔍 ابحث باسم الجولة...">
            </div>

            <select id="cityFilter" class="form-control">
                <option value="all">كل المدن</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $selectedCity === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="sortSelect" class="form-control">
                <option value="date">الأقرب موعداً</option>
                <option value="price-asc">السعر: من الأقل</option>
                <option value="price-desc">السعر: من الأعلى</option>
            </select>
        </div>

        <div class="type-chips" id="typeChips">
            <button class="chip active" data-type="all">الكل</button>
            <?php foreach ($types as $type): ?>
                <button class="chip" data-type="<?= e($type) ?>"><?= e($type) ?></button>
            <?php endforeach; ?>
        </div>

        <p class="results-count muted" id="resultsCount"></p>

        <!-- ========== البطاقات ========== -->
        <div class="cards-grid" id="toursGrid">
            <?php foreach ($tours as $tour): ?>
                <!-- data-* تحفظ بيانات الجولة عشان الـ JS يفلتر ويرتب عليها -->
                <article class="card tour-card"
                         data-city="<?= (int)$tour['city_id'] ?>"
                         data-type="<?= e($tour['type']) ?>"
                         data-title="<?= e($tour['title']) ?>"
                         data-price="<?= (float)$tour['price'] ?>"
                         data-date="<?= e($tour['tour_date']) ?>">
                    <div class="card-img">
                        <img src="images/<?= e($tour['image']) ?>" alt="<?= e($tour['title']) ?>" loading="lazy">
                        <span class="badge"><?= e($tour['type']) ?></span>
                        <?php if ((int)$tour['available_seats'] === 0): ?>
                            <span class="badge badge-full">مكتملة</span>
                        <?php elseif ((int)$tour['available_seats'] <= 5): ?>
                            <span class="badge badge-few">باقي <?= (int)$tour['available_seats'] ?> مقاعد</span>
                        <?php endif; ?>
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
        </div>

        <!-- تظهر لما ما فيه نتائج -->
        <div class="empty-state" id="emptyState" hidden>
            <span>🏜️</span>
            <h3>ما لقينا جولات تطابق بحثك</h3>
            <p class="muted">جرّبي تغيّرين المدينة أو النوع</p>
            <button class="btn btn-outline" id="resetFilters">عرض كل الجولات</button>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
