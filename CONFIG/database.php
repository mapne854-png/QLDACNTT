<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'autohub';
const DB_USER = 'root';
const DB_PASS = '';
const APP_NAME = 'AutoHub';
const APP_TIMEZONE = 'Asia/Ho_Chi_Minh';

date_default_timezone_set(APP_TIMEZONE);

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function money(float|int|string|null $value): string {
    return number_format((float)($value ?? 0), 0, ',', '.') . ' ₫';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('CSRF token không hợp lệ.');
    }
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function status_class(string $status): string {
    $map = [
        'Mới' => 'blue', 'Đang tư vấn' => 'amber', 'Đã đặt cọc' => 'green', 'Đã mua' => 'green',
        'Khách hàng mới' => 'blue', 'Đã liên hệ / lái thử' => 'amber', 'Đang thương lượng' => 'amber', 'Đã chốt' => 'green',
        'Có sẵn' => 'green', 'Đã giữ chỗ' => 'amber', 'Đã bán' => 'gray', 'Đang vận chuyển' => 'blue',
        'Sắp hết' => 'red', 'Đủ tồn' => 'green', 'Đang sửa chữa' => 'amber', 'Hoàn tất' => 'green',
        'Chưa thanh toán' => 'red', 'Một phần' => 'amber', 'Đã thanh toán' => 'green', 'Quá hạn' => 'red',
        'Đã tiếp nhận' => 'blue', 'Đang kiểm tra' => 'amber', 'Chờ phụ tùng' => 'red', 'Đã bàn giao' => 'green',
    ];
    return $map[$status] ?? 'gray';
}

function initials(string $name): string {
    $parts = preg_split('/\s+/u', trim($name));
    $chars = [];
    foreach (array_slice($parts ?: [], -2) as $part) {
        $chars[] = mb_substr($part, 0, 1);
    }
    return mb_strtoupper(implode('', $chars ?: ['A']));
}
