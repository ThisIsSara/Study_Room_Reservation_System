<?php
// PHP authentication functions and a small class for the users table.
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function startUserSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/'
        ]);
        session_start();
    }
}

function currentUser(): ?array
{
    startUserSession();
    return isset($_SESSION['user_id'])
        ? ['id' => (int) $_SESSION['user_id'], 'name' => (string) $_SESSION['user_name']]
        : null;
}

function requireLogin(): void
{
    if (currentUser() === null) {
        header('Location: ../login.html?error=login_required');
        exit;
    }
}

final class UserAuth
{
    public function __construct(private PDO $db) {}

    public function register(string $name, string $email, string $password): bool
    {
        $statement = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)'
        );
        return $statement->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function login(string $email, string $password): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, full_name, password_hash FROM users WHERE email = ? LIMIT 1'
        );
        $statement->execute([$email]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
