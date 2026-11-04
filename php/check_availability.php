
<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$date = $_GET['date'] ?? '';
$room = $_GET['room'] ?? '';

if (!is_string($date) || !is_string($room) || $room === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
$today = new DateTimeImmutable('today', new DateTimeZone('Asia/Riyadh'));

if (!$selectedDate || $selectedDate->format('Y-m-d') !== $date || $selectedDate < $today) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

try {
    $db = database();

    $statement = $db->prepare(
        "SELECT b.start_time, b.end_time
         FROM bookings b
         JOIN rooms r ON b.room_id = r.id
         WHERE r.room_name = ?
         AND r.status = 'available'
         AND b.booking_date = ?
         AND b.status = 'confirmed'"
    );

    $statement->execute([$room, $date]);
    $bookings = $statement->fetchAll();

    $bookedSlots = [];

    foreach ($bookings as $booking) {
        for ($hour = 9; $hour < 17; $hour++) {
            $start = sprintf('%02d:00:00', $hour);
            $end = sprintf('%02d:00:00', $hour + 1);

            if ($booking['start_time'] < $end &&
                $booking['end_time'] > $start) {
                $bookedSlots[] = $hour . '-' . ($hour + 1);
            }
        }
    }

    echo json_encode(array_values(array_unique($bookedSlots)));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
