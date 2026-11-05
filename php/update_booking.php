<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function redirectEdit(int $id, string $error): never {
    header('Location: ../edit-booking.php?id=' . $id . '&error=' . rawurlencode($error));
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}
$user = currentUser();
if ($user === null) {
    header('Location: ../login.html?error=login_required');
    exit;
}
$id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$token = $_POST['csrf_token'] ?? null;
if (!$id || !is_string($token) || !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(400);
    exit('Invalid request');
}
$date = $_POST['booking-date'] ?? null;
$slot = $_POST['time'] ?? null;
$slots = [
    '9-10' => 9, '10-11' => 10, '11-12' => 11, '12-1' => 12,
    '1-2' => 13, '2-3' => 14, '3-4' => 15, '4-5' => 16
];
$tz = new DateTimeZone('Asia/Riyadh');
$chosenDate = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz) : false;
if (!$chosenDate || $chosenDate->format('Y-m-d') !== $date ||
    !is_string($slot) || !isset($slots[$slot])) {
    redirectEdit($id, 'invalid');
}
$hour = $slots[$slot];
$start = sprintf('%02d:00:00', $hour);
$end = sprintf('%02d:00:00', $hour + 1);
$now = new DateTimeImmutable('now', $tz);
if ($chosenDate->setTime($hour, 0) <= $now) {
    redirectEdit($id, 'past');
}
$db = null;
try {
    $db = database();
    $db->beginTransaction();
    // Look up room ID, then use the same room lock as create_booking.php.
    $lookup = $db->prepare('SELECT room_id FROM bookings WHERE id = ? AND user_id = ? AND status = \'confirmed\'');
    $lookup->execute([$id, $user['id']]);
    $booking = $lookup->fetch();
    if (!$booking) {
        $db->rollBack();
        redirectEdit($id, 'not_found');
    }
    $room = $db->prepare("SELECT id FROM rooms WHERE id = ? AND status = 'available' FOR UPDATE");
    $room->execute([(int)$booking['room_id']]);
    if (!$room->fetch()) {
        $db->rollBack();
        redirectEdit($id, 'unavailable');
    }
    $current = $db->prepare("SELECT id, booking_date, start_time, room_id FROM bookings
        WHERE id = ? AND user_id = ? AND status = 'confirmed' FOR UPDATE");
    $current->execute([$id, $user['id']]);
    $original = $current->fetch();
    if (!$original || (int)$original['room_id'] !== (int)$booking['room_id']) {
        $db->rollBack();
        redirectEdit($id, 'not_found');
    }
    $originalStart = new DateTimeImmutable($original['booking_date'] . ' ' . $original['start_time'], $tz);
    if ($originalStart <= $now) {
        $db->rollBack();
        redirectEdit($id, 'past');
    }
    $conflicts = $db->prepare("SELECT id FROM bookings WHERE room_id = ? AND booking_date = ?
        AND status = 'confirmed' AND id <> ? AND start_time < ? AND end_time > ? LIMIT 1");
    $conflicts->execute([(int)$original['room_id'], $date, $id, $end, $start]);
    if ($conflicts->fetch()) {
        $db->rollBack();
        redirectEdit($id, 'conflict');
    }
    $update = $db->prepare("UPDATE bookings SET booking_date = ?, start_time = ?, end_time = ?
        WHERE id = ? AND user_id = ? AND status = 'confirmed'");
    $update->execute([$date, $start, $end, $id, $user['id']]);
    $db->commit();
    header('Location: ../my-bookings.php?updated=1');
    exit;
} catch (PDOException $e) {
    if ($db instanceof PDO && $db->inTransaction()) $db->rollBack();
    error_log('Booking update error: ' . $e->getMessage());
    redirectEdit($id, 'database');
}
