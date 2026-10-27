<?php
declare(strict_types=1);

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS autohub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE autohub");
    echo "Database 'autohub' ready.\n";

    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(30) NOT NULL DEFAULT 'BAN_HANG',
        branch_id INT NULL DEFAULT 1,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Customers
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NULL,
        address VARCHAR(255) NULL,
        source VARCHAR(50) DEFAULT 'Showroom',
        interested_car VARCHAR(100) NULL,
        assigned_to VARCHAR(100) DEFAULT 'Nguyễn Văn Nam',
        status VARCHAR(30) DEFAULT 'Mới',
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Activities
    $pdo->exec("CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NULL,
        type VARCHAR(30) DEFAULT 'call',
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        created_by VARCHAR(100) DEFAULT 'Hệ thống',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Vehicles
    $pdo->exec("CREATE TABLE IF NOT EXISTS vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        brand VARCHAR(50) NOT NULL,
        model VARCHAR(100) NOT NULL,
        version VARCHAR(50) NULL,
        year INT DEFAULT 2026,
        color VARCHAR(30) DEFAULT 'Đen',
        vin VARCHAR(50) UNIQUE NOT NULL,
        engine_no VARCHAR(50) NULL,
        price DECIMAL(15,2) NOT NULL DEFAULT 0,
        cost_price DECIMAL(15,2) NOT NULL DEFAULT 0,
        status VARCHAR(30) DEFAULT 'Có sẵn',
        location VARCHAR(50) DEFAULT 'Kho A',
        image_url VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $pdo->exec("ALTER TABLE vehicles ADD COLUMN image_url VARCHAR(255) NULL AFTER location");
    } catch(Exception $e) {}

    // 5. Sales Opportunities & Test drives
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales_opportunities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        vehicle_id INT NULL,
        employee_id INT NULL,
        stage VARCHAR(50) DEFAULT 'Khách hàng mới',
        value DECIMAL(15,2) DEFAULT 0,
        probability INT DEFAULT 50,
        follow_up DATE NULL,
        note TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS test_drives (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        vehicle_id INT NOT NULL,
        employee_id INT NULL,
        drive_date DATE NOT NULL,
        drive_time TIME NOT NULL,
        status VARCHAR(30) DEFAULT 'Đã đăng ký',
        notes TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 6. Parts & Transactions
    $pdo->exec("CREATE TABLE IF NOT EXISTS parts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sku VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(150) NOT NULL,
        category VARCHAR(50) DEFAULT 'Phụ tùng chung',
        unit VARCHAR(20) DEFAULT 'Cái',
        price DECIMAL(12,2) DEFAULT 0,
        cost_price DECIMAL(12,2) DEFAULT 0,
        stock INT DEFAULT 0,
        min_stock INT DEFAULT 5,
        supplier VARCHAR(100) NULL,
        location VARCHAR(50) DEFAULT 'Kệ A1',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS part_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        part_id INT NOT NULL,
        type VARCHAR(10) NOT NULL, -- IN / OUT
        qty INT NOT NULL,
        unit_price DECIMAL(12,2) DEFAULT 0,
        note TEXT NULL,
        created_by VARCHAR(100) DEFAULT 'Hệ thống',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 7. Service & Repair Orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS repair_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        customer_id INT NOT NULL,
        vehicle_id INT NOT NULL,
        advisor VARCHAR(100) DEFAULT 'Nguyễn Thành Đạt',
        technician VARCHAR(100) DEFAULT 'Trần Quốc Bình',
        mileage INT DEFAULT 0,
        status VARCHAR(30) DEFAULT 'Mới',
        description TEXT NULL,
        subtotal DECIMAL(15,2) DEFAULT 0,
        vat DECIMAL(15,2) DEFAULT 0,
        discount DECIMAL(15,2) DEFAULT 0,
        total DECIMAL(15,2) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS repair_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        repair_order_id INT NOT NULL,
        type VARCHAR(20) DEFAULT 'Công việc',
        code VARCHAR(50) NULL,
        name VARCHAR(150) NOT NULL,
        qty INT DEFAULT 1,
        unit_price DECIMAL(12,2) DEFAULT 0,
        total DECIMAL(15,2) DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 8. Accounting (Invoices, Payments, Expenses)
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        customer_id INT NOT NULL,
        type VARCHAR(30) DEFAULT 'Bán xe',
        subtotal DECIMAL(15,2) DEFAULT 0,
        vat DECIMAL(15,2) DEFAULT 0,
        discount DECIMAL(15,2) DEFAULT 0,
        total DECIMAL(15,2) DEFAULT 0,
        payment_status VARCHAR(30) DEFAULT 'Chưa thanh toán',
        issued_at DATE NOT NULL,
        due_date DATE NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        code VARCHAR(30) UNIQUE NOT NULL,
        amount DECIMAL(15,2) DEFAULT 0,
        method VARCHAR(30) DEFAULT 'Chuyển khoản',
        reference VARCHAR(100) NULL,
        paid_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        category VARCHAR(50) DEFAULT 'Chi phí vận hành',
        amount DECIMAL(15,2) DEFAULT 0,
        recipient VARCHAR(100) NULL,
        description TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 9. Employees & Departments & KPI
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(30) UNIQUE NOT NULL,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NULL,
        email VARCHAR(100) NULL,
        department VARCHAR(50) DEFAULT 'Kinh doanh',
        position VARCHAR(50) DEFAULT 'Tư vấn bán hàng',
        salary DECIMAL(12,2) DEFAULT 15000000,
        target DECIMAL(15,2) DEFAULT 3000000000,
        result DECIMAL(15,2) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 10. Audit logs & Notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(50) NOT NULL,
        entity VARCHAR(50) NOT NULL,
        entity_id INT NULL,
        old_data JSON NULL,
        new_data JSON NULL,
        ip_address VARCHAR(45) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Tables & Schema Migration Ready.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
