<?php
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT t.*, c.name AS city_name
    FROM tours t
    JOIN cities c ON c.id = t.city_id
    WHERE t.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$tour = $stmt->get_result()->fetch_assoc();

if ($tour) {
    $steps    = array_filter(array_map('trim', preg_split('/\s*-\s*/u', $tour['itinerary'] ?? '')));
    $includes = array_filter(array_map('trim', explode('،', $tour['includes'] ?? '')));
    $seats    = (int)$tour['available_seats'];
}

$pageTitle  = $tour ? $tour['title'] : 'الجولة غير موجودة';
$activePage = 'tours';
require_once 'includes/header.php';
?>

<section class="section">
  <div class="container">

  <?php if (!$tour): ?>

    <div class="empty-state">
      <span>🧭</span>
      <h3>الجولة غير موجودة</h3>
      <p class="muted">يمكن الرابط غلط أو الجولة انحذفت</p>
      <a href="tours.php" class="btn">تصفح الجولات</a>
    </div>

  <?php else: ?>

    <nav class="tour-breadcrumb muted">
      <a href="tours.php">الجولات</a> / <?= e($tour['city_name']) ?> / <?= e($tour['title']) ?>
    </nav>

    <!-- الصورة مع العنوان فوقها -->
    <div class="tour-hero">
      <img src="images/<?= e($tour['image']) ?>" alt="<?= e($tour['title']) ?>">
      <div class="tour-hero-content">
        <span class="tour-type"><?= e($tour['type']) ?></span>
        <h1><?= e($tour['title']) ?></h1>
        <p class="tour-hero-sub"><?= e($tour['short_desc']) ?></p>
      </div>
    </div>

    <!-- شريط المعلومات السريعة -->
    <div class="tour-facts-grid">
      <div class="card tour-fact">
        <span class="tour-fact-icon">📅</span>
        <div><small class="muted">التاريخ</small><strong><?= arabic_date($tour['tour_date']) ?></strong></div>
      </div>
      <div class="card tour-fact">
        <span class="tour-fact-icon">⏱</span>
        <div><small class="muted">المدة</small><strong><?= e($tour['duration']) ?></strong></div>
      </div>
      <div class="card tour-fact">
        <span class="tour-fact-icon">🪑</span>
        <div><small class="muted">المقاعد المتبقية</small><strong><?= $seats ?></strong></div>
      </div>
      <div class="card tour-fact">
        <span class="tour-fact-icon">📍</span>
        <div><small class="muted">المدينة</small><strong><?= e($tour['city_name']) ?></strong></div>
      </div>
    </div>

    <div class="tour-layout">

      <!-- المحتوى -->
      <article class="card tour-main">
        <h2>عن الجولة</h2>
        <p class="tour-desc"><?= e($tour['description']) ?></p>

        <?php if ($steps): ?>
          <h2>برنامج الجولة</h2>
          <ol class="tour-steps">
            <?php foreach ($steps as $step): ?>
              <li><?= e($step) ?></li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>

        <?php if ($includes): ?>
          <h2>يشمل</h2>
          <ul class="tour-includes">
            <?php foreach ($includes as $item): ?>
              <li>✓ <?= e($item) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <h2>نقطة التجمع</h2>
        <p>📍 <?= e($tour['meeting_point']) ?></p>
      </article>

      <!-- بطاقة الحجز -->
      <aside class="card tour-sidebar">
        <small class="muted">السعر للشخص</small>
        <span class="price"><?= price($tour['price']) ?></span>

        <p class="tour-seats <?= $seats <= 5 ? 'few' : '' ?>">
          <?php if ($seats === 0): ?>
            الجولة مكتملة
          <?php elseif ($seats <= 5): ?>
            باقي <?= $seats ?> مقاعد فقط!
          <?php else: ?>
            متاح <?= $seats ?> مقعد
          <?php endif; ?>
        </p>

        <?php if ($seats > 0): ?>
          <a href="#booking" class="btn btn-block">احجز الآن</a>
        <?php else: ?>
          <button class="btn btn-block" disabled>الجولة مكتملة</button>
        <?php endif; ?>

        <p class="muted tour-note">الحجز بدون تسجيل حساب</p>
      </aside>

    </div>

  <?php endif; ?>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>