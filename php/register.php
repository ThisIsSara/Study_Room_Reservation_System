<?php
// Receive the registration form and store a new user.
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.html');
    exit;
}

$name = trim((string) ($_POST['fullname'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$confirm = (string) ($_POST['confirm-password'] ?? '');

if ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($password) < 8 || $password !== $confirm) {
    header('Location: ../register.html?error=invalid');
    exit;
}

try {
    $auth = new UserAuth(database());
    $auth->register($name, $email, $password);
    header('Location: ../login.html?registered=1');
} catch (PDOException $exception) {
    // SQLSTATE 23000 commonly indicates duplicate unique email.
    error_log('Registration failed: ' . $exception->getMessage());
    $error = $exception->getCode() === '23000' ? 'duplicate' : 'database';
    header('Location: ../register.html?error=' . $error);
}
exit;
