
<?php

/* =====================================================
   delete_tour.php - Delete a tour
   ===================================================== */

require_once 'auth.php';

/* ---------- Validate request method ---------- */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request method');
    header('Location: dashboard.php');
    exit;
}

/* ---------- Get tour ID ---------- */

$tourId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$tourId || $tourId <= 0) {
    set_flash('error', 'Invalid tour ID');
    header('Location: dashboard.php');
    exit;
}

/* ---------- Check whether the tour exists ---------- */

$stmt = $conn->prepare(
    "SELECT id FROM tours WHERE id = ? LIMIT 1"
);

if (!$stmt) {
    set_flash('error', 'Failed to prepare the query');
    header('Location: dashboard.php');
    exit;
}

$stmt->bind_param('i', $tourId);
$stmt->execute();

$result = $stmt->get_result();
$tour = $result->fetch_assoc();

$stmt->close();

if (!$tour) {
    set_flash('error', 'Tour not found or already deleted');
    header('Location: dashboard.php');
    exit;
}

/* ---------- Delete the tour ---------- */

$stmt = $conn->prepare(
    "DELETE FROM tours WHERE id = ?"
);

if (!$stmt) {
    set_flash('error', 'Failed to prepare the delete operation');
    header('Location: dashboard.php');
    exit;
}

$stmt->bind_param('i', $tourId);

if ($stmt->execute() && $stmt->affected_rows === 1) {
    $stmt->close();

    set_flash('success', 'تم حذف الجولة بنجاح');
    header('Location: dashboard.php');
    exit;
}

$stmt->close();

set_flash('error', 'تعذر حذف الجولة، حاولي مرة أخرى');
header('Location: dashboard.php');
exit;