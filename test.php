<?php
/* =====================================================
   test.php — صفحة تجربة فقط ⚠️ احذفوها قبل التسليم
   تتأكد إن: الاتصال بالقاعدة شغال + الصور تطلع + التصميم مضبوط
   ===================================================== */
require_once 'includes/db.php';

$pageTitle  = 'صفحة التجربة';
$activePage = '';
require_once 'includes/header.php';

$sql = "SELECT t.*, c.name AS city_name, a.full_name AS admin_name
        FROM tours t
        JOIN cities c      ON c.id = t.city_id
        LEFT JOIN admins a ON a.id = t.created_by
        ORDER BY t.tour_date";
$tours = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<section class="section">
    <div class="container">
        <div class="alert alert-success">✅ الاتصال بقاعدة البيانات شغّال — عدد الجولات: <?= count($tours) ?></div>

        <h2 class="section-title">تجربة بطاقات الجولات</h2>
        <p class="section-subtitle">إذا الصور والنصوص طالعة صح، الأساس جاهز وتقدرون تبدون.</p>

        <div class="cards-grid">
            <?php foreach ($tours as $tour): ?>
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
                        <small class="muted">أضافتها: <?= e($tour['admin_name']) ?></small>
                    </div>
                    <div class="card-footer">
                        <span class="price"><?= price($tour['price']) ?></span>
                        <a href="tour.php?id=<?= (int)$tour['id'] ?>" class="btn btn-sm">التفاصيل</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <h2 class="section-title" style="margin-top:50px">تجربة العناصر المشتركة</h2>
        <div class="form-card" style="max-width:560px">
            <div class="alert alert-error">❌ هذا شكل رسالة الخطأ</div>
            <div class="form-group">
                <label>حقل عادي <span class="req">*</span></label>
                <input type="text" class="form-control" placeholder="اكتبي هنا">
            </div>
            <div class="form-group">
                <label>حقل فيه خطأ</label>
                <input type="text" class="form-control invalid" value="05123">
                <span class="error-msg">رقم الجوال لازم يبدأ بـ 05 ويتكون من 10 أرقام</span>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <button class="btn">زر أساسي</button>
                <button class="btn btn-outline">زر ثانوي</button>
                <button class="btn btn-light">زر فاتح</button>
                <button class="btn btn-danger">حذف</button>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
