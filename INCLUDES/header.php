<?php
$flashes = get_flashes();
$pageTitles = [
    'dashboard' => 'Tổng quan hoạt động',
    'customers' => 'CRM / Khách hàng',
    'customer-detail' => 'Chi tiết khách hàng',
    'sales' => 'Bán hàng & Lái thử',
    'vehicles' => 'Kho xe (Inventory)',
    'parts' => 'Kho phụ tùng',
    'service' => 'Dịch vụ / Sửa chữa',
    'invoices' => 'Kế toán & Hóa đơn',
    'employees' => 'Nhân sự & KPI',
    'reports' => 'Báo cáo quản trị'
];
$currentTitle = $pageTitles[$page] ?? 'AutoHub';
$pdo = db();
$stmt = $pdo->prepare('SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 5');
$stmt->execute([user()['id'] ?? 1]);
$notifications = $stmt->fetchAll();
$unread = count(array_filter($notifications, static fn($n) => (int)$n['is_read'] === 0));
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($currentTitle)?> — AutoHub Dealership</title>
<link rel="stylesheet" href="public/css/style.css">
<script>window.APP = { csrf: "<?=e(csrf_token())?>" };</script>
</head>
<body>
<div class="app-shell">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="main-shell">
<header class="topbar">
  <div class="top-left">
    <div class="breadcrumb">AutoHub <span>›</span> <?=e($currentTitle)?></div>
    <h1><?=e($currentTitle)?></h1>
  </div>

  <div class="top-right">
    <div class="global-search">
      <span style="color:#94a3b8">⌕</span>
      <input placeholder="Tìm kiếm nhanh..." onkeydown="if(event.key==='Enter'){location.href='index.php?page=customers&q='+encodeURIComponent(this.value)}">
    </div>

    <button class="icon-btn-round" onclick="togglePanel('notifications')" title="Thông báo">
      🔔<?php if($unread):?><i><?=$unread?></i><?php endif;?>
    </button>

    <div class="user-pill" onclick="togglePanel('profile')" style="cursor:pointer">
      <span class="avatar sm"><?=e(initials(user()['name'] ?? 'Admin'))?></span>
      <div class="user-meta">
        <b><?=e(user()['name'] ?? 'Admin')?></b>
        <small><?=e(user()['role'] ?? 'ADMIN')?></small>
      </div>
      <span style="color:#94a3b8;font-size:10px">⌄</span>
    </div>
  </div>

  <div id="notifications" class="floating-panel hidden">
    <div class="panel-title"><b>Thông báo hệ thống</b><span><?=$unread?> chưa đọc</span></div>
    <?php foreach($notifications as $n): ?>
      <div class="notice <?= $n['is_read'] ? '' : 'unread' ?>" data-notice-id="<?=$n['id']?>">
        <b><?=e($n['title'])?></b>
        <p><?=e($n['message'])?></p>
        <small><?=date('H:i d/m', strtotime($n['created_at']))?></small>
      </div>
    <?php endforeach; ?>
    <?php if (!$notifications): ?><div class="notice"><p>Chưa có thông báo mới.</p></div><?php endif; ?>
  </div>

  <div id="profile" class="floating-panel profile-panel hidden">
    <a href="index.php?page=employees">Hồ sơ cá nhân</a>
    <a href="index.php?page=reports">Báo cáo</a>
    <a href="logout.php">Đăng xuất</a>
  </div>
</header>
<section class="content">
<?php foreach($flashes as $f):?>
  <div class="toast <?=$f['type']?>"><?=e($f['message'])?></div>
<?php endforeach;?>
