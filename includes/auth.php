<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, is_premium FROM tabel_users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function getGuestToken(): string {
    if (empty($_SESSION['guest_token'])) {
        $_SESSION['guest_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['guest_token'];
}

function getGuestCount(): int {
    return (int)($_SESSION['guest_count'] ?? 0);
}

function incrementGuestCount(): void {
    $_SESSION['guest_count'] = getGuestCount() + 1;
}

function guestLimitReached(): bool {
    return !isLoggedIn() && getGuestCount() >= GUEST_LIMIT;
}

function getRemainingGuest(): int {
    return max(0, GUEST_LIMIT - getGuestCount());
}

function loginUser(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, password_hash FROM tabel_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        migrateGuestTransactions($user['id']);
        return true;
    }
    return false;
}

function registerUser(string $name, string $email, string $password): bool|string {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM tabel_users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return 'Email sudah terdaftar.';

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO tabel_users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hash]);
    $newId = (int)$db->lastInsertId();
    $_SESSION['user_id'] = $newId;
    migrateGuestTransactions($newId);
    return true;
}

function logoutUser(): void {
    $_SESSION = [];
    session_destroy();
}

function migrateGuestTransactions(int $userId): void {
    $token = $_SESSION['guest_token'] ?? null;
    if (!$token) return;
    $db = getDB();
    $stmt = $db->prepare("UPDATE tabel_transactions SET user_id = ?, guest_token = NULL WHERE guest_token = ?");
    $stmt->execute([$userId, $token]);
    unset($_SESSION['guest_token'], $_SESSION['guest_count']);
}
function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}
