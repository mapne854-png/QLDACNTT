<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function logged_in(): bool {
    return isset($_SESSION['user']['id']);
}

function user(): array {
    return $_SESSION['user'] ?? [];
}

function require_login(): void {
    if (!logged_in()) {
        redirect('login.php');
    }
}

function is_admin(): bool {
    return in_array(user()['role'] ?? '', ['ADMIN', 'QUAN_LY'], true);
}

function can(string $permission): bool {
    $role = user()['role'] ?? '';
    $map = [
        'ADMIN' => ['*'],
        'QUAN_LY' => ['crm','sales','vehicle','parts','service','accounting','hr','report'],
        'BAN_HANG' => ['crm','sales','vehicle'],
        'DICH_VU' => ['crm','service','parts','accounting'],
        'KE_TOAN' => ['accounting','report'],
        'KHO' => ['vehicle','parts'],
        'NHAN_SU' => ['hr','report'],
    ];
    $perms = $map[$role] ?? [];
    return in_array('*', $perms, true) || in_array($permission, $perms, true);
}

function audit(string $action, string $entity, int|string|null $entityId, mixed $old = null, mixed $new = null): void {
    try {
        $stmt = db()->prepare('INSERT INTO audit_logs(user_id, action, entity, entity_id, old_data, new_data, ip_address) VALUES(?,?,?,?,?,?,?)');
        $stmt->execute([
            user()['id'] ?? null,
            $action,
            $entity,
            $entityId,
            $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
            $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // Audit log fallback
    }
}
