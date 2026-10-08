<?php

/* =====================================================
   edit_tour.php — تعديل جولة
   الجزء 3: CRUD
   ===================================================== */

require_once 'auth.php';

$errors = [];

/* ---------- الحصول على رقم الجولة ---------- */

$tourId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($tourId <= 0) {
    set_flash('error', 'الجولة المطلوبة غير موجودة');
    header('Location: dashboard.php');
    exit;
}

/* ---------- جلب بيانات الجولة ---------- */

$stmt = $conn->prepare("
    SELECT *
    FROM tours
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param('i', $tourId);
$stmt->execute();

$result = $stmt->get_result();
$tour = $result->fetch_assoc();

$stmt->close();

if (!$tour) {
    set_flash('error', 'الجولة المطلوبة غير موجودة');
    header('Location: dashboard.php');
    exit;
}

/* ---------- جلب المدن ---------- */

$citiesResult = $conn->query("
    SELECT id, name
    FROM cities
    ORDER BY name
");

$cities = $citiesResult
    ? $citiesResult->fetch_all(MYSQLI_ASSOC)
    : [];

/* ---------- القيم الحالية ---------- */

$title = $tour['title'];
$type = $tour['type'];
$cityId = (int)$tour['city_id'];
$shortDesc = $tour['short_desc'];
$description = $tour['description'];
$itinerary = $tour['itinerary'] ?? '';
$meetingPoint = $tour['meeting_point'] ?? '';
$includes = $tour['includes'] ?? '';
$tourDate = $tour['tour_date'];
$duration = $tour['duration'];
$priceValue = $tour['price'];
$availableSeats = (int)$tour['available_seats'];
$isFeatured = (int)$tour['is_featured'];
$currentImage = $tour['image'];

/* ---------- معالجة التعديل ---------- */

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

    if (
        $priceValue === '' ||
        !is_numeric($priceValue) ||
        (float)$priceValue <= 0
    ) {
        $errors[] = 'الرجاء إدخال سعر صحيح أكبر من صفر';
    }

    $price = (float)$priceValue;

    if ($availableSeats < 0) {
        $errors[] = 'عدد المقاعد لا يمكن أن يكون أقل من صفر';
    }

    /* ---------- الصورة الجديدة ---------- */

    $newImageName = $currentImage;
    $uploadedNewImage = false;

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

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

            /* ---------- حفظ الصورة الجديدة ---------- */

            if (!$errors) {

                $uploadDir = __DIR__ . '/../images/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = $allowedMimeTypes[$mimeType];

                $newImageName =
                    bin2hex(random_bytes(12)) . '.' . $extension;

                $newImagePath = $uploadDir . $newImageName;

                if (
                    move_uploaded_file(
                        $image['tmp_name'],
                        $newImagePath
                    )
                ) {

                    $uploadedNewImage = true;

                } else {

                    $errors[] = 'تعذر حفظ الصورة الجديدة';
                    $newImageName = $currentImage;
                }
            }
        }
    }

    /* ---------- تحديث الجولة ---------- */

    if (!$errors) {

        $stmt = $conn->prepare("
            UPDATE tours
            SET
                city_id = ?,
                title = ?,
                type = ?,
                short_desc = ?,
                description = ?,
                itinerary = ?,
                meeting_point = ?,
                includes = ?,
                tour_date = ?,
                duration = ?,
                price = ?,
                available_seats = ?,
                image = ?,
                is_featured = ?
            WHERE id = ?
        ");

        if (!$stmt) {

            $errors[] = 'حدث خطأ أثناء تجهيز عملية التعديل';

        } else {

            $stmt->bind_param(
                'isssssssssdisii',
                $cityId,
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
                $newImageName,
                $isFeatured,
                $tourId
            );

            if ($stmt->execute()) {

                /*
                 * إذا تم رفع صورة جديدة بنجاح،
                 * نحذف الصورة القديمة بعد نجاح التعديل.
                 */

                if (
                    $uploadedNewImage &&
                    $currentImage !== '' &&
                    $currentImage !== $newImageName
                ) {

                    $oldImagePath =
                        __DIR__ . '/../images/' . basename($currentImage);

                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $stmt->close();

                set_flash('success', 'تم تعديل الجولة بنجاح');

                header('Location: dashboard.php');
                exit;

            } else {

                /*
                 * إذا فشل التحديث نحذف الصورة الجديدة
                 * حتى لا تبقى صورة بدون استخدام.
                 */

                if ($uploadedNewImage) {

                    $newImagePath =
                        __DIR__ . '/../images/' . $newImageName;

                    if (file_exists($newImagePath)) {
                        unlink($newImagePath);
                    }
                }

                $errors[] = 'حدث خطأ أثناء تعديل الجولة';

                $stmt->close();
            }
        }
    }
}

/* ---------- إعداد الصفحة ---------- */

$pageTitle = 'تعديل جولة';
$activePage = 'dashboard';
$base = '../';

require_once __DIR__ . '/../includes/header.php';

?>

<section class="section">

    <div class="container">

        <div class="dash-head">

            <div>
                <h1>تعديل الجولة</h1>
                <p class="muted">
                    تعديل بيانات الجولة: <?= e($title) ?>
                </p>
            </div>

            <div class="dash-actions">

                <a
                    href="dashboard.php"
                    class="btn btn-outline"
                >
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
                action="edit_tour.php?id=<?= $tourId ?>"
                enctype="multipart/form-data"
                id="editTourForm"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $tourId ?>"
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

                        <option value="">
                            اختاري المدينة
                        </option>

                        <?php foreach ($cities as $city): ?>

                            <option
                                value="<?= (int)$city['id'] ?>"
                                <?= $cityId === (int)$city['id'] ? 'selected' : '' ?>
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

                        <option value="">
                            اختاري نوع الجولة
                        </option>

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
                        value="<?= e((string)$priceValue) ?>"
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

                    <label>
                        الصورة الحالية
                    </label>

                    <div class="current-image-wrapper">

                        <img
                            src="../images/<?= e($currentImage) ?>"
                            alt="<?= e($title) ?>"
                            class="current-tour-image"
                            style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 12px;"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="image">
                        تغيير الصورة
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="muted">
                        اتركي الحقل فارغًا للاحتفاظ بالصورة الحالية.
                        JPG أو PNG أو WEBP — الحد الأقصى 5MB
                    </small>

                </div>


                <div
                    id="imagePreviewContainer"
                    class="image-preview-container"
                    hidden
                >

                    <p class="muted">
                        معاينة الصورة الجديدة:
                    </p>

                    <img
                        id="imagePreview"
                        class="image-preview"
                        src=""
                        alt="معاينة الصورة الجديدة"
                        style="max-width: 300px; max-height: 200px; object-fit: cover; border-radius: 12px;"
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

                        <span>
                            تمييز الجولة كجولة مميزة ⭐
                        </span>

                    </label>

                </div>


                <div class="actions">

                    <button
                        type="submit"
                        class="btn"
                    >
                        حفظ التعديلات
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