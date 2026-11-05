<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/auth.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Method not allowed']);
}

$user = currentUser();
if ($user === null) {
    respond(401, ['error' => 'Please log in first']);
}

$token = $_POST['csrf_token'] ?? null;
if (!is_string($token) || !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)) {
    respond(403, ['error' => 'Invalid security token. Refresh the page and try again.']);
}

$date = $_POST['booking-date'] ?? '';
$room = $_POST['room'] ?? '';
$time = $_POST['time'] ?? '';

if (!is_string($date) || !is_string($room) || !is_string($time)) {
    respond(400, ['error' => 'Invalid booking details']);
}

$slots = [
    '9-10' => [9, 10],
    '10-11' => [10, 11],
    '11-12' => [11, 12],
    '12-1' => [12, 13],
    '1-2' => [13, 14],
    '2-3' => [14, 15],
    '3-4' => [15, 16],
    '4-5' => [16, 17]
];

$timezone = new DateTimeZone('Asia/Riyadh');
$selectedDate = is_string($date)
    ? DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone)
    : false;
$now = new DateTimeImmutable('now', $timezone);

if (!$selectedDate || $selectedDate->format('Y-m-d') !== $date ||
    !isset($slots[$time]) || $room === '') {
    respond(400, ['error' => 'Invalid date, room or time']);
}

[$startHour, $endHour] = $slots[$time];
$start = sprintf('%02d:00:00', $startHour);
$end = sprintf('%02d:00:00', $endHour);
$bookingStart = $selectedDate->setTime($startHour, 0);
if ($bookingStart <= $now) {
    respond(400, ['error' => 'Please select a future time']);
}

$db = null;
try {
    $db = database();
    $db->beginTransaction();

    $roomQuery = $db->prepare(
        "SELECT id FROM rooms
         WHERE room_name = ? AND status = 'available'
         FOR UPDATE"
    );
    $roomQuery->execute([$room]);
    $selectedRoom = $roomQuery->fetch();

    if (!$selectedRoom) {
        $db->rollBack();
        respond(404, ['error' => 'Room is not available']);
    }
    $roomId = (int) $selectedRoom['id'];

    $check = $db->prepare(
        "SELECT id FROM bookings
         WHERE room_id = ? AND booking_date = ?
           AND status = 'confirmed'
           AND start_time < ? AND end_time > ?
         LIMIT 1"
    );
    $check->execute([$roomId, $date, $end, $start]);

    if ($check->fetch()) {
        $db->rollBack();
        respond(409, ['error' => 'This time slot is already booked']);
    }

    $insert = $db->prepare(
        "INSERT INTO bookings
         (user_id, room_id, booking_date, start_time, end_time, status)
         VALUES (?, ?, ?, ?, ?, 'confirmed')"
    );
    $insert->execute([$user['id'], $roomId, $date, $start, $end]);
    $bookingId = (int) $db->lastInsertId();
    $db->commit();

    respond(201, [
        'success' => true,
        'message' => 'Booking confirmed successfully',
        'booking_id' => $bookingId
    ]);
} catch (PDOException $e) {
    if ($db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Booking error: ' . $e->getMessage());
    respond(500, ['error' => 'Database error']);
}
