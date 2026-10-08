<?php
/* =====================================================
   db.php — الاتصال بقاعدة البيانات
   يُستدعى في أول كل صفحة تحتاج القاعدة:
   require_once 'includes/db.php';
   ===================================================== */

// بيانات الاتصال محفوظة في config.php (ملف خاص بكل وحدة، ما ينرفع على GitHub)
if (!file_exists(__DIR__ . '/config.php')) {
    die('<p style="font-family:sans-serif;text-align:center;margin-top:50px">ناقص ملف includes/config.php — انسخي config.example.php وسمّيه config.php وعبّي بيانات الاتصال.</p>');
}
require_once __DIR__ . '/config.php';

// الجلسة: تبدأ هنا مرة وحدة لكل الصفحات (نحتاجها للدخول ورسائل النجاح)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    $flags = DB_SSL ? MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT : 0;
    if (DB_SSL) {
        $conn->ssl_set(null, null, null, null, null);
    }
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, null, $flags);
    $conn->set_charset('utf8mb4');   // عشان العربي يطلع صح
} catch (mysqli_sql_exception $e) {
    // لا نعرض تفاصيل الخطأ للزائر (أمان)
    error_log('DB connection failed: ' . $e->getMessage());
    die('<p style="font-family:sans-serif;text-align:center;margin-top:50px">عذراً، تعذّر الاتصال بقاعدة البيانات.</p>');
}

/* ---------- دوال مساعدة يستخدمها الكل ---------- */

// حماية من XSS: أي نص جاي من القاعدة أو من المستخدم يمر من هنا قبل العرض
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// تنسيق السعر: 450 → 450 ريال
function price($amount) {
    return number_format((float)$amount) . ' ريال';
}

// تنسيق التاريخ: 2026-12-10 → 10 ديسمبر 2026
function arabic_date($date) {
    $months = ['', 'يناير','فبراير','مارس','أبريل','مايو','يونيو',
               'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
}

/* ---------- رسائل النجاح والخطأ (Flash) ----------
   تُحفظ في الجلسة وتظهر مرة وحدة في الصفحة اللي بعدها.
   الاستخدام بعد أي عملية:
       set_flash('success', 'تمت إضافة الجولة بنجاح');
       header('Location: dashboard.php'); exit;
   وفي الصفحة اللي تعرضها:
       <?php show_flash(); ?>
*/
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $class = $f['type'] === 'success' ? 'alert-success' : 'alert-error';
        echo '<div class="alert ' . $class . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

// هل فيه مشرفة مسجلة دخول؟
function is_admin() {
    return !empty($_SESSION['admin_id']);
}
