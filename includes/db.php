<?php
if (!file_exists(__DIR__ . '/config.php')) {
    error_log('Missing includes/config.php — copy config.example.php and fill in the connection details.');
    die('<p style="font-family:sans-serif;text-align:center;margin-top:50px">عذراً، الموقع غير متاح حالياً. يرجى المحاولة لاحقاً.</p>');
}
require_once __DIR__ . '/config.php';

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
    @$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, null, $flags);
    $conn->set_charset('utf8mb4');   
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    die('<p style="font-family:sans-serif;text-align:center;margin-top:50px"> تعذّر الاتصال بقاعدة البيانات.</p>');
}



function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function price($amount) {
    return number_format((float)$amount) . ' ريال';
}

function arabic_date($date) {
    $months = ['', 'يناير','فبراير','مارس','أبريل','مايو','يونيو',
               'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
}


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

function is_admin() {
    return !empty($_SESSION['admin_id']);
}
