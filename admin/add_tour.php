<?php

/* =====================================================
   add_tour.php — إضافة جولة جديدة
   الجزء 3: CRUD
   ===================================================== */

require_once 'auth.php';

$errors = [];

/* ---------- جلب المدن ---------- */

$citiesResult = $conn->query("
    SELECT id, name
    FROM cities
    ORDER BY name
");

$cities = $citiesResult
    ? $citiesResult->fetch_all(MYSQLI_ASSOC)
    : [];

/* ---------- القيم الافتراضية ---------- */

$title = '';
$type = '';
$cityId = '';
$shortDesc = '';
$description = '';
$itinerary = '';
$meetingPoint = '';
$includes = '';
$tourDate = '';
$duration = '';
$priceValue = '';
$availableSeats = '';
$isFeatured = 0;

/* ---------- معالجة الإرسال ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $cityId = (int)($_POST['city_id'] ?? 0);
    $shortDesc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $itinerary = trim($_POST['itinerary'] ?? '');
    $meetingPoint = trim($_POST['meeting_point'] ?? '');
    $includes = trim($_POST['includes'] ?? '');
    $tourDate = trim($_POST['tour_date'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $priceValue = trim($_POST['price'] ?? '');
    $availableSeats = (int)($_POST['available_seats'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    /* ---------- التحقق من البيانات ---------- */

    if ($title === '') {
        $errors[] = 'الرجاء إدخال اسم الجولة';
    } elseif (mb_strlen($title) > 100) {
        $errors[] = 'اسم الجولة يجب ألا يتجاوز 100 حرف';
    }

    $allowedTypes = [
        'تاريخية',
        'طبيعة',
        'مغامرات',
        'ثقافية'
    ];

    if (!in_array($type, $allowedTypes, true)) {
        $errors[] = 'الرجاء اختيار نوع الجولة';
    }

    if ($cityId <= 0) {
        $errors[] = 'الرجاء اختيار المدينة';
    }

    if ($shortDesc === '') {
        $errors[] = 'الرجاء إدخال وصف مختصر للجولة';
    } elseif (mb_strlen($shortDesc) > 200) {
        $errors[] = 'الوصف المختصر يجب ألا يتجاوز 200 حرف';
    }

    if ($description === '') {
        $errors[] = 'الرجاء إدخال وصف الجولة';
    }

    if ($tourDate === '') {
        $errors[] = 'الرجاء اختيار تاريخ الجولة';
    }

    if ($duration === '') {
        $errors[] = 'الرجاء إدخال مدة الجولة';
    } elseif (mb_strlen($duration) > 30) {
        $errors[] = 'مدة الجولة يجب ألا تتجاوز 30 حرف';
    }

    if ($priceValue === '' || !is_numeric($priceValue) || (float)$priceValue <= 0) {
        $errors[] = 'الرجاء إدخال سعر صحيح أكبر من صفر';
    }

    $price = (float)$priceValue;

    if ($availableSeats < 0) {
        $errors[] = 'عدد المقاعد لا يمكن أن يكون أقل من صفر';
    }

    /* ---------- التحقق من الصورة ---------- */

    $imageName = '';

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {

        $errors[] = 'الرجاء اختيار صورة للجولة';

    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

        $errors[] = 'حدث خطأ أثناء رفع الصورة';

    } else {

        $image = $_FILES['image'];

        /* الحد الأقصى 5MB */

        if ($image['size'] > 5 * 1024 * 1024) {
            $errors[] = 'حجم الصورة يجب ألا يتجاوز 5MB';
        }

        /* التحقق من نوع الملف الحقيقي */

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($image['tmp_name']);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            $errors[] = 'نوع الصورة غير مسموح. استخدمي JPG أو PNG أو WEBP';
        }

        if (!$errors) {

            $uploadDir = __DIR__ . '/../images/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = $allowedMimeTypes[$mimeType];

            /*
             * اسم عشوائي للصورة حتى لا تتكرر أسماء الصور
             */

            $imageName = bin2hex(random_bytes(12)) . '.' . $extension;

            $imagePath = $uploadDir . $imageName;

            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                $errors[] = 'تعذر حفظ الصورة';
            }
        }
    }

    /* ---------- إضافة الجولة إلى قاعدة البيانات ---------- */

    if (!$errors) {

        $adminId = (int)$_SESSION['admin_id'];

        $stmt = $conn->prepare("
            INSERT INTO tours (
                city_id,
                created_by,
                title,
                type,
                short_desc,
                description,
                itinerary,
                meeting_point,
                includes,
                tour_date,
                duration,
                price,
                available_seats,
                image,
                is_featured
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {

            $errors[] = 'حدث خطأ أثناء تجهيز عملية الإضافة';

        } else {

            /*
             * 2 أرقام + 9 نصوص + رقم عشري + رقم صحيح
             * + نص للصورة + رقم صحيح
             */

            $stmt->bind_param(
                'iisssssssssdisi',
                $cityId,
                $adminId,
                $title,
                $type,
                $shortDesc,
                $description,
                $itinerary,
                $meetingPoint,
                $includes,
                $tourDate,
                $duration,
                $price,
                $availableSeats,
                $imageName,
                $isFeatured
            );

            if ($stmt->execute()) {

                set_flash('success', 'تمت إضافة الجولة بنجاح');

                $stmt->close();

                header('Location: dashboard.php');
                exit;

            } else {

                /*
                 * إذا فشلت الإضافة نحذف الصورة التي تم رفعها
                 * حتى لا تبقى صورة بدون جولة في السيرفر.
                 */

                if ($imageName !== '') {
                    $uploadedImagePath = __DIR__ . '/../images/' . $imageName;

                    if (file_exists($uploadedImagePath)) {
                        unlink($uploadedImagePath);
                    }
                }

                $errors[] = 'حدث خطأ أثناء إضافة الجولة إلى قاعدة البيانات';

                $stmt->close();
            }
        }
    }
}

/* ---------- إعداد الصفحة ---------- */

$pageTitle = 'إضافة جولة';
$activePage = 'dashboard';
$base = '../';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="section">

    <div class="container">

        <div class="dash-head">

            <div>
                <h1>إضافة جولة جديدة</h1>
                <p class="muted">أضيفي جولة جديدة إلى منصة وِجهة</p>
            </div>

            <div class="dash-actions">
                <a href="dashboard.php" class="btn btn-outline">
                    العودة للوحة التحكم
                </a>
            </div>

        </div>

        <?php if ($errors): ?>

            <div class="alert alert-error">

                <?php foreach ($errors as $error): ?>

                    <div><?= e($error) ?></div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div class="form-card">

            <form
                method="POST"
                action="add_tour.php"
                enctype="multipart/form-data"
                id="addTourForm"
            >

                <div class="form-group">

                    <label for="title">
                        اسم الجولة
                        <span class="req">*</span>
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        value="<?= e($title) ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="city_id">
                        المدينة
                        <span class="req">*</span>
                    </label>

                    <select
                        id="city_id"
                        name="city_id"
                        class="form-control"
                        required
                    >

                        <option value="">اختاري المدينة</option>

                        <?php foreach ($cities as $city): ?>

                            <option
                                value="<?= (int)$city['id'] ?>"
                                <?= (string)$cityId === (string)$city['id'] ? 'selected' : '' ?>
                            >
                                <?= e($city['name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="type">
                        نوع الجولة
                        <span class="req">*</span>
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="form-control"
                        required
                    >

                        <option value="">اختاري نوع الجولة</option>

                        <option
                            value="تاريخية"
                            <?= $type === 'تاريخية' ? 'selected' : '' ?>
                        >
                            تاريخية
                        </option>

                        <option
                            value="طبيعة"
                            <?= $type === 'طبيعة' ? 'selected' : '' ?>
                        >
                            طبيعة
                        </option>

                        <option
                            value="مغامرات"
                            <?= $type === 'مغامرات' ? 'selected' : '' ?>
                        >
                            مغامرات
                        </option>

                        <option
                            value="ثقافية"
                            <?= $type === 'ثقافية' ? 'selected' : '' ?>
                        >
                            ثقافية
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label for="short_desc">
                        الوصف المختصر
                        <span class="req">*</span>
                    </label>

                    <input
                        type="text"
                        id="short_desc"
                        name="short_desc"
                        class="form-control"
                        value="<?= e($shortDesc) ?>"
                        maxlength="200"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="description">
                        وصف الجولة
                        <span class="req">*</span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        class="form-control"
                        required
                    ><?= e($description) ?></textarea>

                </div>


                <div class="form-group">

                    <label for="itinerary">
                        البرنامج / المسار
                    </label>

                    <textarea
                        id="itinerary"
                        name="itinerary"
                        class="form-control"
                    ><?= e($itinerary) ?></textarea>

                </div>


                <div class="form-group">

                    <label for="meeting_point">
                        نقطة التجمع
                    </label>

                    <input
                        type="text"
                        id="meeting_point"
                        name="meeting_point"
                        class="form-control"
                        value="<?= e($meetingPoint) ?>"
                        maxlength="150"
                    >

                </div>


                <div class="form-group">

                    <label for="includes">
                        يشمل
                    </label>

                    <input
                        type="text"
                        id="includes"
                        name="includes"
                        class="form-control"
                        value="<?= e($includes) ?>"
                        maxlength="255"
                        placeholder="مثال: النقل، المرشد السياحي، الوجبات"
                    >

                </div>


                <div class="form-group">

                    <label for="tour_date">
                        تاريخ الجولة
                        <span class="req">*</span>
                    </label>

                    <input
                        type="date"
                        id="tour_date"
                        name="tour_date"
                        class="form-control"
                        value="<?= e($tourDate) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="duration">
                        مدة الجولة
                        <span class="req">*</span>
                    </label>

                    <input
                        type="text"
                        id="duration"
                        name="duration"
                        class="form-control"
                        value="<?= e($duration) ?>"
                        maxlength="30"
                        placeholder="مثال: 4 ساعات"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="price">
                        السعر
                        <span class="req">*</span>
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-control"
                        value="<?= e($priceValue) ?>"
                        min="0.01"
                        step="0.01"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="available_seats">
                        المقاعد المتاحة
                        <span class="req">*</span>
                    </label>

                    <input
                        type="number"
                        id="available_seats"
                        name="available_seats"
                        class="form-control"
                        value="<?= e((string)$availableSeats) ?>"
                        min="0"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="image">
                        صورة الجولة
                        <span class="req">*</span>
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                        required
                    >

                    <small class="muted">
                        JPG أو PNG أو WEBP — الحد الأقصى 5MB
                    </small>

                </div>


                <div
                    id="imagePreviewContainer"
                    class="image-preview-container"
                    hidden
                >

                    <p class="muted">معاينة الصورة:</p>

                    <img
                        id="imagePreview"
                        class="image-preview"
                        src=""
                        alt="معاينة صورة الجولة"
                    >

                </div>


                <div class="form-group">

                    <label class="featured-check">

                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            <?= $isFeatured ? 'checked' : '' ?>
                        >

                        <span>تمييز الجولة كجولة مميزة ⭐</span>

                    </label>

                </div>


                <div class="actions">

                    <button
                        type="submit"
                        class="btn"
                    >
                        إضافة الجولة
                    </button>

                    <a
                        href="dashboard.php"
                        class="btn btn-outline"
                    >
                        إلغاء
                    </a>

                </div>

            </form>

        </div>

    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>