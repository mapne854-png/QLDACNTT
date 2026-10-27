<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_login();

$pdo = db();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    // 1. CRM Customer actions
    if ($action === 'customer_create' && can('crm')) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $source = trim($_POST['source'] ?? 'Showroom');
        $interested = trim($_POST['interested_car'] ?? '');
        $assigned = trim($_POST['assigned_to'] ?? user()['name']);
        $status = trim($_POST['status'] ?? 'Mới');
        $notes = trim($_POST['notes'] ?? '');

        if ($name && $phone) {
            $code = 'KH-' . rand(1000, 9999);
            $stmt = $pdo->prepare('INSERT INTO customers (code, name, phone, email, address, source, interested_car, assigned_to, status, notes) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$code, $name, $phone, $email, $address, $source, $interested, $assigned, $status, $notes]);
            $cid = $pdo->lastInsertId();

            $pdo->prepare('INSERT INTO activities (customer_id, type, title, description, created_by) VALUES (?,?,?,?,?)')
                ->execute([$cid, 'customer', 'Tạo hồ sơ khách hàng mới', "Khách hàng $name - $phone đã được tạo.", user()['name']]);

            audit('CREATE', 'Customer', $cid, null, ['name' => $name, 'phone' => $phone]);
            flash('success', "Đã thêm khách hàng $name thành công.");
        }
        redirect('index.php?page=customers');
    }

    if ($action === 'customer_delete' && can('crm')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM customers WHERE id=?')->execute([$id]);
            audit('DELETE', 'Customer', $id, null, null);
            flash('success', 'Đã xóa khách hàng.');
        }
        redirect('index.php?page=customers');
    }

    if ($action === 'appointment_create') {
        $cid = (int)($_POST['customer_id'] ?? 0);
        $vid = (int)($_POST['vehicle_id'] ?? 0);
        $type = trim($_POST['type'] ?? 'Lái thử');
        $date = $_POST['appointment_date'] ?? date('Y-m-d');
        $time = $_POST['appointment_time'] ?? '09:30';
        $note = trim($_POST['note'] ?? '');

        if ($cid > 0) {
            if ($vid > 0) {
                $pdo->prepare('INSERT INTO test_drives (customer_id, vehicle_id, employee_id, drive_date, drive_time, notes) VALUES (?,?,?,?,?,?)')
                    ->execute([$cid, $vid, user()['id'], $date, $time, $note]);
            }
            $pdo->prepare('INSERT INTO activities (customer_id, type, title, description, created_by) VALUES (?,?,?,?,?)')
                ->execute([$cid, 'appointment', "Đăng ký $type", "Lịch hẹn ngày $date lúc $time. Ghi chú: $note", user()['name']]);

            flash('success', 'Đã tạo lịch hẹn thành công.');
            redirect('index.php?page=customer-detail&id=' . $cid);
        }
        redirect('index.php?page=customers');
    }

    // 2. Sales Opportunities actions
    if ($action === 'sales_create' && can('sales')) {
        $cid = (int)($_POST['customer_id'] ?? 0);
        $vid = (int)($_POST['vehicle_id'] ?? 0);
        $emp = (int)($_POST['employee_id'] ?? user()['id']);
        $value = (float)($_POST['value'] ?? 0);
        $prob = (int)($_POST['probability'] ?? 50);
        $follow = $_POST['follow_up'] ?: null;
        $stage = $_POST['stage'] ?? 'Khách hàng mới';
        $note = trim($_POST['note'] ?? '');

        if ($cid > 0) {
            $pdo->prepare('INSERT INTO sales_opportunities (customer_id, vehicle_id, employee_id, stage, value, probability, follow_up, note) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$cid, $vid, $emp, $stage, $value, $prob, $follow, $note]);
            
            audit('CREATE', 'SalesOpportunity', $pdo->lastInsertId(), null, ['customer_id' => $cid, 'value' => $value]);
            flash('success', 'Đã tạo cơ hội bán hàng thành công.');
        }
        redirect('index.php?page=sales');
    }

    // 3. Vehicles Inventory actions
    if ($action === 'vehicle_create' && can('vehicle')) {
        $brand = trim($_POST['brand'] ?? 'AutoHub');
        $model = trim($_POST['model'] ?? '');
        $version = trim($_POST['version'] ?? '');
        $year = (int)($_POST['year'] ?? 2026);
        $vin = trim($_POST['vin'] ?? '');
        $color = trim($_POST['color'] ?? 'Đen');
        $price = (float)($_POST['price'] ?? 0);
        $cost = (float)($_POST['cost_price'] ?? 0);
        $status = $_POST['status'] ?? 'Có sẵn';
        $loc = trim($_POST['location'] ?? 'Showroom chính');

        if ($model && $vin) {
            $code = 'XE-' . rand(100, 999);
            $pdo->prepare('INSERT INTO vehicles (code, brand, model, version, year, color, vin, price, cost_price, status, location) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$code, $brand, $model, $version, $year, $color, $vin, $price, $cost, $status, $loc]);
            audit('CREATE', 'Vehicle', $pdo->lastInsertId(), null, ['vin' => $vin, 'model' => $model]);
            flash('success', "Đã thêm xe $brand $model ($vin) vào kho.");
        }
        redirect('index.php?page=vehicles');
    }

    if ($action === 'vehicle_status' && can('vehicle')) {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Có sẵn';
        if ($id > 0) {
            $pdo->prepare('UPDATE vehicles SET status=? WHERE id=?')->execute([$status, $id]);
            audit('UPDATE_STATUS', 'Vehicle', $id, null, ['status' => $status]);
            flash('success', 'Cập nhật trạng thái xe thành công.');
        }
        redirect('index.php?page=vehicles');
    }

    // 4. Parts Inventory actions
    if ($action === 'part_create' && can('parts')) {
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $cat = trim($_POST['category'] ?? 'Phụ tùng chung');
        $unit = trim($_POST['unit'] ?? 'Cái');
        $stock = (int)($_POST['stock'] ?? 0);
        $min = (int)($_POST['min_stock'] ?? 5);
        $price = (float)($_POST['price'] ?? 0);
        $cost = (float)($_POST['cost_price'] ?? 0);
        $sup = trim($_POST['supplier'] ?? 'AutoHub OEM Parts');
        $loc = trim($_POST['location'] ?? 'Kệ A-01');

        if ($sku && $name) {
            $pdo->prepare('INSERT INTO parts (sku, name, category, unit, price, cost_price, stock, min_stock, supplier, location) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$sku, $name, $cat, $unit, $price, $cost, $stock, $min, $sup, $loc]);
            audit('CREATE', 'Part', $pdo->lastInsertId(), null, ['sku' => $sku]);
            flash('success', "Đã thêm mã phụ tùng $sku thành công.");
        }
        redirect('index.php?page=parts');
    }

    if ($action === 'part_adjust' && can('parts')) {
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'IN';
        $qty = (int)($_POST['qty'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if ($id > 0 && $qty > 0) {
            if ($type === 'IN') {
                $pdo->prepare('UPDATE parts SET stock = stock + ? WHERE id=?')->execute([$qty, $id]);
            } else {
                $pdo->prepare('UPDATE parts SET stock = GREATEST(0, stock - ?) WHERE id=?')->execute([$qty, $id]);
            }
            $pdo->prepare('INSERT INTO part_transactions (part_id, type, qty, note, created_by) VALUES (?,?,?,?,?)')
                ->execute([$id, $type, $qty, $note, user()['name']]);
            audit('STOCK_ADJUST', 'Part', $id, null, ['type' => $type, 'qty' => $qty]);
            flash('success', 'Đã cập nhật tồn kho phụ tùng.');
        }
        redirect('index.php?page=parts');
    }

    // 5. Service / Repair Orders actions
    if ($action === 'repair_create' && can('service')) {
        $cid = (int)($_POST['customer_id'] ?? 0);
        $vid = (int)($_POST['vehicle_id'] ?? 0);
        $adv = trim($_POST['advisor'] ?? user()['name']);
        $tech = trim($_POST['technician'] ?? 'Kỹ thuật viên');
        $km = (int)($_POST['mileage'] ?? 0);
        $disc = (float)($_POST['discount'] ?? 0);
        $desc = trim($_POST['description'] ?? '');
        $items = $_POST['items'] ?? [];

        if ($cid > 0 && $vid > 0) {
            $code = 'RO-' . date('Ymd') . '-' . rand(100, 999);
            $subtotal = 0.0;

            foreach ($items as $it) {
                $q = (int)($it['qty'] ?? 1);
                $p = (float)($it['unit_price'] ?? 0);
                $subtotal += ($q * $p);
            }
            $vat = $subtotal * 0.1;
            $total = max(0, $subtotal + $vat - $disc);

            $pdo->prepare('INSERT INTO repair_orders (code, customer_id, vehicle_id, advisor, technician, mileage, status, description, subtotal, vat, discount, total) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$code, $cid, $vid, $adv, $tech, $km, 'Đã tiếp nhận', $desc, $subtotal, $vat, $disc, $total]);
            $roid = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO repair_items (repair_order_id, type, code, name, qty, unit_price, total) VALUES (?,?,?,?,?,?,?)');
            foreach ($items as $it) {
                $q = (int)($it['qty'] ?? 1);
                $p = (float)($it['unit_price'] ?? 0);
                $t = $q * $p;
                if (!empty($it['name'])) {
                    $itemStmt->execute([$roid, $it['type'] ?? 'Công việc', $it['code'] ?? '', $it['name'], $q, $p, $t]);
                }
            }

            // Tự động tạo hóa đơn cho dịch vụ
            $invCode = 'INV-' . date('Ymd') . '-' . rand(100, 999);
            $pdo->prepare('INSERT INTO invoices (code, customer_id, type, subtotal, vat, discount, total, payment_status, issued_at) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$invCode, $cid, 'Dịch vụ', $subtotal, $vat, $disc, $total, 'Chưa thanh toán', date('Y-m-d')]);

            audit('CREATE', 'RepairOrder', $roid, null, ['code' => $code, 'total' => $total]);
            flash('success', "Đã tạo lệnh sửa chữa $code thành công.");
        }
        redirect('index.php?page=service');
    }

    if ($action === 'repair_status' && can('service')) {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Đang sửa chữa';
        if ($id > 0) {
            $pdo->prepare('UPDATE repair_orders SET status=? WHERE id=?')->execute([$status, $id]);
            audit('UPDATE_STATUS', 'RepairOrder', $id, null, ['status' => $status]);
            flash('success', 'Đã chuyển trạng thái lệnh sửa chữa.');
        }
        redirect('index.php?page=service');
    }

    // 6. Accounting & Invoice actions
    if ($action === 'invoice_create' && can('accounting')) {
        $cid = (int)($_POST['customer_id'] ?? 0);
        $type = $_POST['type'] ?? 'Bán xe';
        $subtotal = (float)($_POST['subtotal'] ?? 0);
        $disc = (float)($_POST['discount'] ?? 0);
        $vat = $subtotal * 0.1;
        $total = max(0, $subtotal + $vat - $disc);

        if ($cid > 0) {
            $code = 'INV-' . date('Ymd') . '-' . rand(100, 999);
            $pdo->prepare('INSERT INTO invoices (code, customer_id, type, subtotal, vat, discount, total, payment_status, issued_at) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$code, $cid, $type, $subtotal, $vat, $disc, $total, 'Chưa thanh toán', date('Y-m-d')]);
            audit('CREATE', 'Invoice', $pdo->lastInsertId(), null, ['code' => $code, 'total' => $total]);
            flash('success', "Đã phát hành hóa đơn $code.");
        }
        redirect('index.php?page=invoices');
    }

    if ($action === 'invoice_pay' && can('accounting')) {
        $id = (int)($_POST['id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'Chuyển khoản';
        $ref = trim($_POST['reference'] ?? '');

        if ($id > 0 && $amount > 0) {
            $pcode = 'PAY-' . date('Ymd') . '-' . rand(100, 999);
            $pdo->prepare('INSERT INTO payments (invoice_id, code, amount, method, reference) VALUES (?,?,?,?,?)')
                ->execute([$id, $pcode, $amount, $method, $ref]);

            $pdo->prepare("UPDATE invoices SET payment_status='Đã thanh toán' WHERE id=?")->execute([$id]);
            audit('PAYMENT', 'Invoice', $id, null, ['amount' => $amount, 'method' => $method]);
            flash('success', "Ghi nhận phiếu thu $pcode thành công.");
        }
        redirect('index.php?page=invoices');
    }

    // 7. HR Employees actions
    if ($action === 'employee_create' && can('hr')) {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dept = trim($_POST['department'] ?? 'Kinh doanh');
        $pos = trim($_POST['position'] ?? 'Tư vấn bán hàng');
        $target = (float)($_POST['target'] ?? 3000000000);
        $result = (float)($_POST['result'] ?? 0);

        if ($name) {
            $code = 'NV-' . rand(100, 999);
            $pdo->prepare('INSERT INTO employees (code, name, phone, email, department, position, target, result) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$code, $name, $phone, $email, $dept, $pos, $target, $result]);
            audit('CREATE', 'Employee', $pdo->lastInsertId(), null, ['name' => $name]);
            flash('success', "Đã thêm nhân viên $name.");
        }
        redirect('index.php?page=employees');
    }
}

// Page Routing
$page = trim($_GET['page'] ?? 'dashboard');

$pageMap = [
    'dashboard'       => ['file' => 'DASHBOARD/index.php', 'perm' => 'dashboard'],
    'customers'       => ['file' => 'CUSTOMER/index.php', 'perm' => 'crm'],
    'customer-detail' => ['file' => 'CUSTOMER/detail.php', 'perm' => 'crm'],
    'sales'           => ['file' => 'SALE/index.php', 'perm' => 'sales'],
    'vehicles'        => ['file' => 'VEHICLES/index.php', 'perm' => 'vehicle'],
    'parts'           => ['file' => 'PARTS/index.php', 'perm' => 'parts'],
    'service'         => ['file' => 'SERVICES/index.php', 'perm' => 'service'],
    'invoices'        => ['file' => 'INVOICES/index.php', 'perm' => 'accounting'],
    'employees'       => ['file' => 'EMPLOYSEES/index.php', 'perm' => 'hr'],
    'reports'         => ['file' => 'REPORTS/index.php', 'perm' => 'report'],
];

if (!isset($pageMap[$page])) {
    $page = 'dashboard';
}

$pageInfo = $pageMap[$page];

if ($page !== 'dashboard' && !can($pageInfo['perm'])) {
    flash('danger', 'Bạn không có quyền truy cập trang này.');
    redirect('index.php?page=dashboard');
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/' . $pageInfo['file'];
include __DIR__ . '/includes/footer.php';
