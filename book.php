<?php

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tours.php');
    exit;
}

$tourId  = (int)($_POST['tour_id'] ?? 0);
$name    = trim($_POST['customer_name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$persons = (int)($_POST['persons'] ?? 0);

$back = 'tour.php?id=' . $tourId;   

$error = '';
if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
    $error = 'الرجاء إدخال الاسم بشكل صحيح';
} elseif (!preg_match('/^([\x{0621}-\x{064A}\s]+|[A-Za-z\s]+)$/u', $name)) {
    $error = 'الاسم لازم يكون بالعربي كامل أو بالإنجليزي كامل، بدون أرقام أو رموز';
} elseif (!preg_match('/^05\d{8}$/', $phone)) {
    $error = 'رقم الجوال لازم يبدأ بـ 05 ويتكون من 10 أرقام';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $error = 'الرجاء إدخال بريد إلكتروني صحيح';
} elseif ($persons < 1) {
    $error = 'عدد الأشخاص لازم يكون 1 على الأقل';
}

if ($error) {
    set_flash('error', $error);
    header('Location: ' . $back . '#booking');
    exit;
}


try {
    $conn->begin_transaction();

    $stmt = $conn->prepare('SELECT title, price, available_seats, tour_date FROM tours WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $tourId);
    $stmt->execute();
    $tour = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tour) {
        $conn->rollback();
        set_flash('error', 'الجولة غير موجودة');
        header('Location: tours.php');
        exit;
    }

    $today = (new DateTime('today', new DateTimeZone('Asia/Riyadh')))->format('Y-m-d');
    if ($tour['tour_date'] < $today) {
        $conn->rollback();
        set_flash('error', 'عذراً، انتهى موعد هذه الجولة ولا يمكن الحجز فيها');
        header('Location: ' . $back);
        exit;
    }

    if ($persons > (int)$tour['available_seats']) {
        $conn->rollback();
        set_flash('error', 'عذراً، المقاعد المتبقية ' . (int)$tour['available_seats'] . ' فقط');
        header('Location: ' . $back . '#booking');
        exit;
    }

    $total = $persons * (float)$tour['price'];

    $stmt = $conn->prepare('
        INSERT INTO bookings (tour_id, customer_name, phone, email, persons, total_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param('isssid', $tourId, $name, $phone, $email, $persons, $total);
    $stmt->execute();
    $bookingId = $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare('UPDATE tours SET available_seats = available_seats - ? WHERE id = ?');
    $stmt->bind_param('ii', $persons, $tourId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    set_flash('success', 'تم الحجز بنجاح! رقم حجزك #' . $bookingId . ' — الإجمالي ' . price($total) . '. بنتواصل معك لتأكيد الحجز.');
    header('Location: ' . $back);
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    error_log('Booking failed: ' . $e->getMessage());   
    set_flash('error', 'عذراً، صار خطأ أثناء الحجز. حاول مرة ثانية.');
    header('Location: ' . $back . '#booking');
    exit;
}
