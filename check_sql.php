<?php
require_once __DIR__ . '/CONFIG/database.php';

try {
    $pdo = db();
    echo "========================================================\n";
    echo " KẾT NỐI CƠ SỞ DỮ LIỆU MYSQL: THÀNH CÔNG (SUCCESS)\n";
    echo "========================================================\n";
    echo "Database Server: " . DB_HOST . "\n";
    echo "Database Name:   " . DB_NAME . "\n";
    echo "Database User:   " . DB_USER . "\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Danh sách các bảng SQL đang hoạt động trong CSDL 'autohub':\n";
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  - Bảng `$t`: $count bản ghi\n";
    }
    echo "========================================================\n";
} catch (Exception $e) {
    echo "KẾT NỐI LỖI: " . $e->getMessage() . "\n";
}
