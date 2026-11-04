<?php
// Receive the login form and create a PHP session.
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: ../login.html?error=invalid');
    exit;
}

try {
    $auth = new UserAuth(database());
    $user = $auth->login($email, $password);
    if ($user === null) {
        header('Location: ../login.html?error=credentials');
        exit;
    }

    startUserSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['full_name'];
    header('Location: ../index.html');
} catch (PDOException $exception) {
    error_log('Login failed: ' . $exception->getMessage());
    header('Location: ../login.html?error=database');
}
exit;
