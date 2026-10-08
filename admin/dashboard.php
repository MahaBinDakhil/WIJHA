<?php
/* =====================================================
   dashboard.php — لوحة التحكم (الجزء 1)
   تعرض الإحصائيات + جدول كل الجولات (Read)
   أزرار التعديل والحذف توصّل لصفحات الجزء 3
   ===================================================== */
require_once 'auth.php';   // ← حماية: لازم تسجيل دخول

// ---------- الإحصائيات ----------
$adminId = (int)$_SESSION['admin_id'];
$stats = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM tours)                          AS tours,
        (SELECT COUNT(*) FROM bookings)                       AS bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'جديد') AS new_bookings,
        (SELECT COUNT(*) FROM tours WHERE created_by = $adminId) AS my_tours
")->fetch_assoc();

// ---------- كل الجولات مع اسم المدينة والمشرفة ----------
$tours = $conn->query("
    SELECT t.id, t.title, t.image, t.type, t.tour_date, t.price, t.available_seats,
           c.name AS city_name, a.full_name AS admin_name
    FROM tours t
    JOIN cities c      ON c.id = t.city_id
    LEFT JOIN admins a ON a.id = t.created_by
    ORDER BY t.tour_date
")->fetch_all(MYSQLI_ASSOC);

$pageTitle  = 'لوحة التحكم';
$activePage = 'dashboard';
$base       = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">

        <?php show_flash(); ?>

        <div class="dash-head">
            <div>
                <h1>لوحة التحكم</h1>
                <p class="muted">مرحباً <?= e($_SESSION['admin_name']) ?> 👋</p>
            </div>
            <div class="dash-actions">
                <a href="bookings.php" class="btn btn-outline">إدارة الحجوزات</a>
                <a href="add_tour.php" class="btn">+ إضافة جولة</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">🧭</span>
                <strong><?= (int)$stats['tours'] ?></strong>
                <span>جولة</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🎟️</span>
                <strong><?= (int)$stats['bookings'] ?></strong>
                <span>حجز</span>
            </div>
            <div class="stat-card highlight">
                <span class="stat-icon">🔔</span>
                <strong><?= (int)$stats['new_bookings'] ?></strong>
                <span>حجز جديد</span>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⭐</span>
                <strong><?= (int)$stats['my_tours'] ?></strong>
                <span>جولة أضفتِها</span>
            </div>
        </div>

        <h2 class="section-title">الجولات</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>الجولة</th>
                        <th>المدينة</th>
                        <th>التاريخ</th>
                        <th>السعر</th>
                        <th>المقاعد</th>
                        <th>أضافتها</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$tours): ?>
                    <tr><td colspan="8" class="text-center muted">لا توجد جولات بعد</td></tr>
                <?php endif; ?>

                <?php foreach ($tours as $t): ?>
                    <tr>
                        <td><img src="../images/<?= e($t['image']) ?>" alt=""></td>
                        <td>
                            <strong><?= e($t['title']) ?></strong><br>
                            <span class="badge"><?= e($t['type']) ?></span>
                        </td>
                        <td><?= e($t['city_name']) ?></td>
                        <td><?= arabic_date($t['tour_date']) ?></td>
                        <td><?= price($t['price']) ?></td>
                        <td><?= (int)$t['available_seats'] ?></td>
                        <td><?= e($t['admin_name'] ?? '—') ?></td>
                        <td>
                            <div class="actions">
                                <a href="edit_tour.php?id=<?= (int)$t['id'] ?>" class="btn btn-sm">تعديل</a>
                                <!-- الحذف بـ POST مو رابط عادي (أأمن) — صفحة delete_tour.php من الجزء 3 -->
                                <form method="POST" action="delete_tour.php" class="delete-form">
                                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
