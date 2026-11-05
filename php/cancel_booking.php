<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$user = currentUser();

if ($user === null) {
    header('Location: ../login.html?error=login_required');
    exit;
}

$bookingId = filter_input(
    INPUT_POST,
    'booking_id',
    FILTER_VALIDATE_INT
);

$token = $_POST['csrf_token'] ?? '';

if (
    !$bookingId ||
    !is_string($token) ||
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    http_response_code(400);
    exit('Invalid cancellation request');
}

try {
    $db = database();

    $stmt = $db->prepare("
        UPDATE bookings
        SET status = 'cancelled'
        WHERE id = ?
          AND user_id = ?
          AND status = 'confirmed'
          AND TIMESTAMP(booking_date, start_time) >
              CONVERT_TZ(
                  UTC_TIMESTAMP(),
                  '+00:00',
                  '+03:00'
              )
    ");

    $stmt->execute([
        $bookingId,
        $user['id']
    ]);

    if ($stmt->rowCount() === 0) {
        header('Location: ../my-bookings.php?error=not_found');
        exit;
    }

    header('Location: ../my-bookings.php?cancelled=1');
    exit;

} catch (PDOException $e) {
    error_log('Cancellation error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to cancel booking.');
}
