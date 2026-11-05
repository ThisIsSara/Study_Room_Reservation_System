<?php
declare(strict_types=1);
require_once __DIR__ . '/php/auth.php';
$user = currentUser();
if ($user === null) {
    header('Location: login.html?error=login_required');
    exit;
}
date_default_timezone_set('Asia/Riyadh');
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
try {
    $db = database();
    $stmt = $db->prepare("SELECT b.id, b.booking_date, b.start_time, b.end_time,
        r.room_name, r.location FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        WHERE b.user_id = ? AND b.status = 'confirmed'
          AND TIMESTAMP(b.booking_date, b.end_time) > ?
        ORDER BY b.booking_date ASC, b.start_time ASC");
    $stmt->execute([$user['id'], date('Y-m-d H:i:s')]);
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('My bookings error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load bookings. Please try again.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Study Rooms</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <div class="logo"><span class="logo-icon">📖</span><span>Study Rooms</span></div>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="rooms.html">Rooms</a></li>
            <li><a href="my-bookings.php" class="active">My Bookings</a></li>
        </ul>
        <div class="nav-buttons">
            <a href="login.html" class="login-btn">Login</a>
            <a href="register.html" class="register-btn">Register</a>
        </div>
    </nav>
</header>
<main class="my-bookings-page">
    <section class="my-bookings-header">
        <p class="small-title">YOUR RESERVATIONS</p>
        <h1>My Bookings</h1>
        <p>View and manage your upcoming study room reservations.</p>
    </section>
    <?php if (empty($bookings)): ?>
        <div class="booking-item">
            <div class="booking-item-info">
                <h2>No Upcoming Bookings</h2>
                <p>You have no upcoming reservations.</p>
                <a href="rooms.html" class="edit-booking-btn">Browse Rooms</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <?php
                $roomName = (string)$booking['room_name'];
                $imageName = strtolower($roomName);
                if (!in_array($imageName, ['a1','a2','b1','b2','c1','c2'], true)) {
                    $imageName = 'a1';
                }
                $image = 'images/room-' . $imageName . '.jpg';
                $date = date('d M Y', strtotime($booking['booking_date']));
                $start = date('g:i A', strtotime($booking['start_time']));
                $end = date('g:i A', strtotime($booking['end_time']));
                $startTimestamp = strtotime($booking['booking_date'] . ' ' . $booking['start_time']);
                $endTimestamp = strtotime($booking['booking_date'] . ' ' . $booking['end_time']);
                $duration = ($endTimestamp - $startTimestamp) / 3600;
                $durationText = $duration == 1 ? '1 Hour' : $duration . ' Hours';
            ?>
            <div class="booking-item">
                <img src="<?= escape($image) ?>" alt="Room <?= escape($roomName) ?>">
                <div class="booking-item-info">
                    <div class="booking-item-top">
                        <div>
                            <h2>Room <?= escape($roomName) ?></h2>
                            <p><?= escape((string)$booking['location']) ?></p>
                        </div>
                        <span class="booking-status">Upcoming</span>
                    </div>
                    <div class="booking-details-row">
                        <div><span>Date</span><strong><?= escape($date) ?></strong></div>
                        <div><span>Time</span><strong><?= escape($start) ?> - <?= escape($end) ?></strong></div>
                        <div><span>Duration</span><strong><?= escape($durationText) ?></strong></div>
                    </div>
                    <div class="booking-actions">
                        <a href="edit-booking.php?id=<?= (int)$booking['id'] ?>" class="edit-booking-btn">Edit</a>
                        <form action="php/cancel_booking.php" method="POST" style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= escape((string)$_SESSION['csrf_token']) ?>">
                            <button type="submit" class="cancel-booking-btn">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<footer>
    <div class="footer-content">
        <div class="logo footer-logo"><span>📖</span><span>Study Rooms</span></div>
        <p>University Study Room Reservation System</p>
    </div>
</footer>
<script src="js/session-ui.js"></script>
<script>
const params = new URLSearchParams(window.location.search);
if (params.get('cancelled') === '1') alert('Booking cancelled successfully!');
if (params.get('updated') === '1') alert('Booking updated successfully!');
if (params.get('error') === 'not_found') alert('Booking could not be cancelled.');
if (params.has('cancelled') || params.has('updated') || params.has('error')) {
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
</body>
</html>
