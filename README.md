# Study Room Reservation System

A web application for reserving university study rooms, developed for the CS486 Web Applications course.

## Project Overview

The system allows students to browse study rooms, check available time slots, and manage their reservations. Each room has its own capacity and location, and bookings are stored in a MySQL database.

## Main Features

- Register a new account and log in
- Browse study rooms and filter rooms by capacity or name
- View room details and availability
- Book a room for an available one-hour time slot
- View upcoming reservations
- Edit or cancel a reservation before its start time
- Prevent overlapping bookings for the same room and time

## Technologies Used

- HTML and CSS for the pages and layout
- JavaScript for form interaction and availability updates
- PHP for authentication and booking operations
- MySQL for storing users, rooms, and reservations
- PDO and prepared statements for database access

## Requirements

- PHP 8 or later with PDO MySQL and mbstring enabled
- MySQL 8
- A web browser

## How to Run

1. Download or clone the repository.
2. Start MySQL and open MySQL Workbench.
3. Import `database/study_room.sql` into a **new or empty** database. This creates the `study_room` database, its tables, and six sample study rooms.
4. Configure the MySQL connection if needed. The defaults in `php/db.php` are:
   - Host: `localhost`
   - Database: `study_room`
   - User: `root`
   - Password: empty

   You can override these values using the `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` environment variables.
5. Open a terminal in the project's root folder and run:

   ```bash
   php -S localhost:8000
   ```

6. Open `http://localhost:8000/index.html` in your browser.
7. Register an account, log in, and try booking a room.

If `php` is not in your system's PATH, run the command with the full path to your PHP executable instead.

**Important:** The SQL setup file uses `DROP TABLE IF EXISTS` and is intended for initial setup only. Importing it again into an existing database can remove existing accounts and bookings.

## Project Structure

```text
css/                Stylesheets
images/             Room and website images
js/                 Front-end JavaScript
php/                PHP authentication and booking endpoints
database/           MySQL database setup
index.html          Home page
rooms.html          Room listing
room-details.html   Room information
book-room.php       Booking form
my-bookings.php     Upcoming reservations
edit-booking.php    Edit an existing reservation
login.html          Login form
register.html       Registration form
```

## Booking Rules

- Time slots are one hour long, from 9:00 AM to 5:00 PM.
- A confirmed booking blocks the same room and time slot for other users.
- Users can edit or cancel only their own upcoming reservations.
- Booking dates and times are checked using the Asia/Riyadh time zone.

## Course

CS486 – Web Applications
