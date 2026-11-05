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
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: my-bookings.php');
    exit;
}
function e(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
try {
    $db = database();
    $stmt = $db->prepare("SELECT b.id, b.booking_date, b.start_time, b.end_time,
        r.room_name, r.location FROM bookings b JOIN rooms r ON r.id = b.room_id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'
        AND TIMESTAMP(b.booking_date, b.start_time) > ? LIMIT 1");
    $stmt->execute([$id, $user['id'], date('Y-m-d H:i:s')]);
    $booking = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Edit booking lookup: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load booking.');
}
if (!$booking) {
    http_response_code(404);
    exit('Booking not found or no longer editable. <a href="my-bookings.php">Back to My Bookings</a>');
}
$name = (string)$booking['room_name'];
$imageName = strtolower($name);
if (!in_array($imageName, ['a1','a2','b1','b2','c1','c2'], true)) $imageName = 'a1';
$timeValues = [
    '9-10' => [9,10], '10-11' => [10,11], '11-12' => [11,12],
    '12-1' => [12,13], '1-2' => [13,14], '2-3' => [14,15],
    '3-4' => [15,16], '4-5' => [16,17]
];
$currentHour = (int)substr((string)$booking['start_time'], 0, 2);
$currentSlot = array_search($currentHour, array_map(fn($t) => $t[0], $timeValues), true);
$messages = [
    'invalid' => 'Please select a valid date and time.',
    'past' => 'You can only edit a booking before it starts.',
    'not_found' => 'This booking is no longer editable.',
    'unavailable' => 'This room is currently unavailable.',
    'conflict' => 'This time slot is already booked. Choose another.',
    'database' => 'Unable to save changes. Please try again.'
];
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Booking - Study Rooms</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><nav class="navbar">
<div class="logo"><span class="logo-icon">📖</span><span>Study Rooms</span></div>
<ul class="nav-links"><li><a href="index.html">Home</a></li><li><a href="rooms.html">Rooms</a></li><li><a class="active" href="my-bookings.php">My Bookings</a></li></ul>
<div class="nav-buttons"><a href="login.html" class="login-btn">Login</a><a href="register.html" class="register-btn">Register</a></div>
</nav></header>
<main class="edit-booking-page">
<a href="my-bookings.php" class="back-link">← Back to My Bookings</a>
<div class="edit-booking-container">
<div class="edit-summary-card">
<img src="images/room-<?= e($imageName) ?>.jpg" alt="Room <?= e($name) ?>">
<div class="edit-summary-info">
<p class="small-title">CURRENT BOOKING</p>
<h1>Room <?= e($name) ?></h1>
<p><?= e((string)$booking['location']) ?></p>
<div class="current-booking-info">
<div><span>Current Date</span><strong><?= e(date('d M Y', strtotime($booking['booking_date']))) ?></strong></div>
<div><span>Current Time</span><strong><?= e(date('g:i A', strtotime($booking['start_time']))) ?> - <?= e(date('g:i A', strtotime($booking['end_time']))) ?></strong></div>
</div></div></div>
<div class="edit-form-card">
<p class="small-title">UPDATE RESERVATION</p>
<h2>Edit Booking</h2>
<p class="edit-description">Choose a new date and one-hour time slot.</p>
<?php if (is_string($error) && isset($messages[$error])): ?>
<p role="alert" style="color:#a12b2b;font-weight:600;"><?= e($messages[$error]) ?></p>
<?php endif; ?>
<p id="availability-error" role="alert" style="color:#a12b2b;display:none;">Could not check availability. Please try again.</p>
<form id="edit-booking-form" action="php/update_booking.php" method="POST" data-room-id="<?= e($name) ?>" data-original-date="<?= e($booking['booking_date']) ?>" data-original-slot="<?= e((string)$currentSlot) ?>">
<input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<div class="booking-form-group"><label for="edit-date">Select New Date</label>
<input type="date" id="edit-date" name="booking-date" value="<?= e($booking['booking_date']) ?>" required></div>
<div class="booking-form-group"><label>Select New Time</label><div class="time-slots">
<?php foreach ($timeValues as $slot => [$start, $end]): ?>
<label class="time-slot"><input type="radio" name="time" value="<?= e($slot) ?>" <?= $slot === $currentSlot ? 'checked' : '' ?> disabled>
<span><?= e(date('g:i A', strtotime(sprintf('%02d:00:00', $start)))) ?> - <?= e(date('g:i A', strtotime(sprintf('%02d:00:00', $end)))) ?></span></label>
<?php endforeach; ?>
</div></div>
<div class="edit-note">You can edit or cancel your booking only before the reservation starts.</div>
<div class="edit-buttons"><a href="my-bookings.php" class="cancel-edit-button">Cancel</a>
<button type="submit" class="save-changes-button" id="save-changes" disabled>Save Changes</button></div>
</form></div></div></main>
<footer><div class="footer-content"><div class="logo footer-logo"><span>📖</span><span>Study Rooms</span></div><p>University Study Room Reservation System</p></div></footer>
<script src="js/session-ui.js"></script>
<script>
(() => {
 const form = document.getElementById('edit-booking-form');
 const dateInput = document.getElementById('edit-date');
 const save = document.getElementById('save-changes');
 const error = document.getElementById('availability-error');
 const radios = Array.from(form.querySelectorAll('input[name="time"]'));
 const originalDate = form.dataset.originalDate;
 const originalSlot = form.dataset.originalSlot;
 const room = form.dataset.roomId;
 const now = new Date();
 const today = [now.getFullYear(), String(now.getMonth()+1).padStart(2,'0'), String(now.getDate()).padStart(2,'0')].join('-');
 dateInput.min = today;
 let currentRequest = 0;
 let ready = false;
 function disableAll() {
   radios.forEach(r => {r.checked = false; r.disabled = true; r.closest('.time-slot').style.opacity = '0.4';});
   save.disabled = true; ready = false;
 }
 async function refresh() {
   const request = ++currentRequest;
   const preferred = form.querySelector('input[name="time"]:checked')?.value || originalSlot;
   disableAll();
   error.style.display = 'none';
   if (!dateInput.value) return;
   try {
     const url = 'php/check_availability.php?date=' + encodeURIComponent(dateInput.value) + '&room=' + encodeURIComponent(room);
     const response = await fetch(url, {cache:'no-store'});
     const blocked = await response.json();
     if (request !== currentRequest) return;
     if (!response.ok || !Array.isArray(blocked)) throw new Error('Availability failed');
     radios.forEach(r => {
       // The current booking is returned as occupied, but is available to its owner for edits.
       const ownSlot = dateInput.value === originalDate && r.value === originalSlot;
       const isBlocked = blocked.includes(r.value) && !ownSlot;
       r.disabled = isBlocked;
       r.closest('.time-slot').style.opacity = isBlocked ? '0.4' : '1';
       if (r.value === preferred && !isBlocked) r.checked = true;
     });
     ready = true;
     save.disabled = false;
   } catch (e) {
     if (request !== currentRequest) return;
     error.style.display = 'block';
   }
 }
 dateInput.addEventListener('change', refresh);
 form.addEventListener('submit', event => {
   const selected = form.querySelector('input[name="time"]:checked');
   if (!ready || !selected || selected.disabled) {
     event.preventDefault();
     alert('Please select an available time slot.');
     return;
   }
   save.disabled = true;
 });
 refresh();
})();
</script>
</body></html>
