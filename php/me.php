<?php
// Return whether the visitor is logged in.
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$user = currentUser();
echo json_encode([
    'loggedIn' => $user !== null,
    'name' => $user['name'] ?? null
]);
