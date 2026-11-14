<?php

require_once 'auth.php';

$statuses = ['جديد', 'مؤكد', 'ملغي'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)($_POST['id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    $redirect = 'bookings.php';
    if (isset($_GET['status']) && in_array($_GET['status'], $statuses, true)) {
        $redirect .= '?status=' . urlencode($_GET['status']);
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare('SELECT tour_id, persons, status FROM bookings WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$booking) {
            throw new RuntimeException('الحجز غير موجود');
        }

        $returnSeats = false;

        switch ($action) {
            case 'confirm':
                if ($booking['status'] !== 'جديد') {
                    throw new RuntimeException('يمكن تأكيد الحجوزات الجديدة فقط');
                }
                $newStatus = 'مؤكد';
                $stmt = $conn->prepare('UPDATE bookings SET status = ? WHERE id = ?');
                $stmt->bind_param('si', $newStatus, $bookingId);
                $stmt->execute();
                $msg = 'تم تأكيد الحجز #' . $bookingId;
                break;

            case 'cancel':
                if ($booking['status'] === 'ملغي') {
                    throw new RuntimeException('الحجز ملغي من قبل');
                }
                $newStatus = 'ملغي';
                $stmt = $conn->prepare('UPDATE bookings SET status = ? WHERE id = ?');
                $stmt->bind_param('si', $newStatus, $bookingId);
                $stmt->execute();
                $returnSeats = true;
                $msg = 'تم إلغاء الحجز #' . $bookingId . ' وإرجاع المقاعد للجولة';
                break;

            case 'delete':
                $stmt = $conn->prepare('DELETE FROM bookings WHERE id = ?');
                $stmt->bind_param('i', $bookingId);
                $stmt->execute();
                $returnSeats = ($booking['status'] !== 'ملغي');
                $msg = 'تم حذف الحجز #' . $bookingId;
                break;

            default:
                throw new RuntimeException('إجراء غير معروف');
        }
        $stmt->close();

        if ($returnSeats) {
            $stmt = $conn->prepare('UPDATE tours SET available_seats = available_seats + ? WHERE id = ?');
            $stmt->bind_param('ii', $booking['persons'], $booking['tour_id']);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        set_flash('success', $msg);

    } catch (RuntimeException $e) {
        $conn->rollback();
        set_flash('error', $e->getMessage());
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        error_log('Booking action failed: ' . $e->getMessage());
        set_flash('error', 'عذراً، صار خطأ. حاولي مرة ثانية.');
    }

    header('Location: ' . $redirect);
    exit;
}

$filter = $_GET['status'] ?? '';
if (!in_array($filter, $statuses, true)) {
    $filter = '';
}

$counts = ['جديد' => 0, 'مؤكد' => 0, 'ملغي' => 0];
$result = $conn->query('SELECT status, COUNT(*) AS n FROM bookings GROUP BY status');
while ($row = $result->fetch_assoc()) {
    $counts[$row['status']] = (int)$row['n'];
}
$total = array_sum($counts);

$sql = '
    SELECT b.*, t.title AS tour_title, t.tour_date
    FROM bookings b
    JOIN tours t ON t.id = b.tour_id
';
if ($filter) {
    $stmt = $conn->prepare($sql . ' WHERE b.status = ? ORDER BY b.created_at DESC');
    $stmt->bind_param('s', $filter);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $bookings = $conn->query($sql . ' ORDER BY b.created_at DESC')->fetch_all(MYSQLI_ASSOC);
}

$statusClass = ['جديد' => 'status-new', 'مؤكد' => 'status-confirmed', 'ملغي' => 'status-cancelled'];

$pageTitle  = 'إدارة الحجوزات';
$activePage = 'dashboard';
$base       = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">

        <?php show_flash(); ?>

        <div class="dash-head">
            <div>
                <h1>إدارة الحجوزات</h1>
                <p class="muted">تأكيد الحجوزات الجديدة أو إلغاؤها أو حذفها</p>
            </div>
            <div class="dash-actions">
                <a href="dashboard.php" class="btn btn-outline">← لوحة التحكم</a>
            </div>
        </div>

        <div class="type-chips bookings-filter">
            <a href="bookings.php" class="chip <?= $filter === '' ? 'active' : '' ?>">الكل (<?= $total ?>)</a>
            <?php foreach ($statuses as $s): ?>
                <a href="bookings.php?status=<?= urlencode($s) ?>"
                   class="chip <?= $filter === $s ? 'active' : '' ?>">
                    <?= e($s) ?> (<?= $counts[$s] ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <div class="table-wrap">
            <table class="table bookings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>الجولة</th>
                        <th>الأشخاص</th>
                        <th>الإجمالي</th>
                        <th>تاريخ الحجز</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$bookings): ?>
                    <tr><td colspan="8" class="text-center muted">لا توجد حجوزات</td></tr>
                <?php endif; ?>

                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><strong><?= (int)$b['id'] ?></strong></td>
                        <td>
                            <strong><?= e($b['customer_name']) ?></strong>
                            <div class="booking-contact muted">
                                <span dir="ltr"><?= e($b['phone']) ?></span><br>
                                <span dir="ltr"><?= e($b['email']) ?></span>
                            </div>
                        </td>
                        <td>
                            <?= e($b['tour_title']) ?>
                            <div class="muted booking-contact">📅 <?= arabic_date($b['tour_date']) ?></div>
                        </td>
                        <td><?= (int)$b['persons'] ?></td>
                        <td><?= price($b['total_price']) ?></td>
                        <td><?= arabic_date($b['created_at']) ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass[$b['status']] ?? '' ?>">
                                <?= e($b['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <?php if ($b['status'] === 'جديد'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn btn-sm">تأكيد</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($b['status'] !== 'ملغي'): ?>
                                    <form method="POST" data-confirm="هل تريدين إلغاء الحجز #<?= (int)$b['id'] ?>؟ بترجع المقاعد للجولة.">
                                        <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <button type="submit" class="btn btn-sm btn-outline">إلغاء</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" data-confirm="هل أنتِ متأكدة من حذف الحجز #<?= (int)$b['id'] ?>؟ لا يمكن التراجع.">
                                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
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