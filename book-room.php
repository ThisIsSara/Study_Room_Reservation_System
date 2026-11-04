<?php
require_once __DIR__ . '/php/auth.php';
if (currentUser() === null) {
    header('Location: login.html?error=login_required');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Room - Study Rooms</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <script src="js/booking.js"></script>


    <!-- Navigation Bar -->
    <header>
        <nav class="navbar">

            <div class="logo">
                <span class="logo-icon">📖</span>
                <span>Study Rooms</span>
            </div>

            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="rooms.html" class="active">Rooms</a></li>
                <li><a href="my-bookings.php">My Bookings</a></li>
            </ul>

            <div class="nav-buttons">
                <a href="login.html" class="login-btn">Login</a>
                <a href="register.html" class="register-btn">Register</a>
            </div>

        </nav>
    </header>


    <!-- Booking Page -->
    <main class="booking-page">

        <a href="room-details.html" class="back-link">
            ← Back to Room Details
        </a>


        <div class="booking-container">

            <!-- Room Summary -->
            <div class="booking-room-card">

                <img
                    src="images/room-a1.jpg"
                    alt="Room A1">

                <div class="booking-room-info">

                    <p class="small-title">
                        SELECTED ROOM
                    </p>

                    <h1>Room A1</h1>

                    <p>
                        Capacity: 4 Students
                    </p>

                    <p>
                        Location: Library - 1st Floor
                    </p>

                    <div class="booking-facilities">

                        <span>Wi-Fi</span>

                        <span>Whiteboard</span>

                        <span>Power Outlets</span>

                    </div>

                </div>

            </div>


            <!-- Booking Form -->
            <div class="booking-form-card">

                <p class="small-title">
                    RESERVATION
                </p>

                <h2>Book Room A1</h2>

                <p class="booking-description">
                    Select a date and one available
                    one-hour time slot.
                </p>


                <form data-room-id="A1">

                    <!-- Date -->
                    <div class="booking-form-group">

                        <label for="booking-date">
                            Select Date
                        </label>

                        <input
                            type="date"
                            id="booking-date"
                            name="booking-date"
                            required>

                    </div>


                    <!-- Time Slots -->
                    <div class="booking-form-group">

                        <label>
                            Select Time
                        </label>

                        <div class="time-slots">

                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="9-10">

                                <span>
                                    9:00 AM - 10:00 AM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="10-11">

                                <span>
                                    10:00 AM - 11:00 AM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="11-12">

                                <span>
                                    11:00 AM - 12:00 PM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="12-1">

                                <span>
                                    12:00 PM - 1:00 PM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="1-2">

                                <span>
                                    1:00 PM - 2:00 PM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="2-3">

                                <span>
                                    2:00 PM - 3:00 PM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="3-4">

                                <span>
                                    3:00 PM - 4:00 PM
                                </span>
                            </label>


                            <label class="time-slot">
                                <input
                                    type="radio"
                                    name="time"
                                    value="4-5">

                                <span>
                                    4:00 PM - 5:00 PM
                                </span>
                            </label>

                        </div>

                    </div>


                    <div class="booking-note">

                        Each reservation is limited to
                        one hour.

                    </div>


                    <button
                        type="submit"
                        class="confirm-booking-button">

                        Confirm Booking

                    </button>

                </form>

            </div>

        </div>

    </main>


    <!-- Footer -->
    <footer>

        <div class="footer-content">

            <div class="logo footer-logo">
                <span>📖</span>
                <span>Study Rooms</span>
            </div>

            <p>
                University Study Room Reservation System
            </p>

        </div>

    </footer>

    <script src="js/session-ui.js"></script>
</body>

</html>
