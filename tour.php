<?php
/* =====================================================
   tour.php — تفاصيل الجولة + نموذج الحجز (الجزء 2)
   ===================================================== */
require_once 'includes/db.php';

// رقم الجولة من الرابط: tour.php?id=7
$id = (int)($_GET['id'] ?? 0);

// نجيب الجولة مع اسم مدينتها (Prepared Statement)
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
    // نقسم البرنامج على "-" عشان نعرضه خطوات مرقمة
    $steps    = array_filter(array_map('trim', preg_split('/\s*-\s*/u', $tour['itinerary'] ?? '')));
    // نقسم "يشمل" على الفاصلة العربية
    $includes = array_filter(array_map('trim', explode('،', $tour['includes'] ?? '')));
    $seats    = (int)$tour['available_seats'];
}

$pageTitle  = $tour ? $tour['title'] : 'الجولة غير موجودة';
$activePage = 'tours';
require_once 'includes/header.php';
?>

<section class="section">
  <div class="container">

  <?php show_flash(); ?>

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

      <!-- بطاقة السعر -->
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

    <!-- نموذج الحجز -->
    <?php if ($seats > 0): ?>
    <div class="card booking-box" id="booking">
      <h2>احجز مقعدك</h2>
      <p class="muted">عبّي بياناتك وبنتواصل معك لتأكيد الحجز</p>

      <form action="book.php" method="POST" id="bookingForm" class="booking-form" novalidate
            data-price="<?= (float)$tour['price'] ?>" data-seats="<?= $seats ?>">

        <input type="hidden" name="tour_id" value="<?= (int)$tour['id'] ?>">

        <div class="booking-grid">
          <div class="form-group">
            <label for="customer_name">الاسم الكامل <span class="req">*</span></label>
            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="مثال: سارة محمد">
            <span class="error-msg"></span>
          </div>

          <div class="form-group">
            <label for="phone">رقم الجوال <span class="req">*</span></label>
            <input type="tel" id="phone" name="phone" class="form-control" placeholder="05xxxxxxxx"
                   maxlength="10" inputmode="numeric" dir="ltr">
            <span class="error-msg"></span>
          </div>

          <div class="form-group">
            <label for="email">البريد الإلكتروني <span class="req">*</span></label>
            <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" dir="ltr">
            <span class="error-msg"></span>
          </div>

          <div class="form-group">
            <label for="persons">عدد الأشخاص <span class="req">*</span></label>
            <input type="number" id="persons" name="persons" class="form-control" value="1" min="1" max="<?= $seats ?>">
            <span class="error-msg"></span>
          </div>
        </div>

        <div class="booking-total">
          <span>الإجمالي</span>
          <strong id="bookingTotal"><?= price($tour['price']) ?></strong>
        </div>

        <button type="submit" class="btn btn-block">تأكيد الحجز</button>
      </form>
    </div>
    <?php endif; ?>

  <?php endif; ?>

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>