<?php
$nav = [
    ['dashboard', '▦', 'Tổng quan (Dashboard)', 'dashboard'],
    ['customers', '♙', 'CRM & Khách hàng', 'crm'],
    ['sales', '▤', 'Bán hàng & Lái thử', 'sales'],
    ['vehicles', '▰', 'Kho xe (Inventory)', 'vehicle'],
    ['parts', '◈', 'Kho phụ tùng', 'parts'],
    ['service', '⚒', 'Dịch vụ & Sửa chữa', 'service'],
    ['invoices', '▣', 'Kế toán & Hóa đơn', 'accounting'],
    ['employees', '♙', 'Nhân sự & KPI', 'hr'],
    ['reports', '◫', 'Báo cáo quản trị', 'report'],
];
?>
<aside class="sidebar" id="sidebar">
  <div class="brand">
    <div class="brand-icon">A</div>
    <div>
      <b>AutoHub</b>
      <small>QUẢN LÝ HÃNG XE</small>
    </div>
  </div>

  <div class="branch-select">
    <span class="status-dot"></span>
    <div>
      <b>AutoHub Sài Gòn</b>
      <small>Chi nhánh chính</small>
    </div>
  </div>

  <nav>
    <?php foreach ($nav as [$key, $icon, $label, $perm]): ?>
      <?php if ($key === 'dashboard' || can($perm)): ?>
        <a class="nav-item <?= ($page === $key || ($key === 'customers' && $page === 'customer-detail')) ? 'active' : '' ?>" href="index.php?page=<?= e($key) ?>">
          <span class="nav-icon"><?= $icon ?></span>
          <span><?= e($label) ?></span>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-bottom">
    <a class="nav-item" href="#" onclick="showToast('info','Trung tâm hỗ trợ AutoHub 24/7 Hotline: 1900-8888'); return false;">
      <span class="nav-icon">?</span> Hỗ trợ kỹ thuật
    </a>
    <div class="sidebar-user">
      <span class="avatar sm dark"><?= e(initials(user()['name'] ?? 'AutoHub')) ?></span>
      <div style="flex:1">
        <b><?= e(user()['name'] ?? 'Admin') ?></b>
        <small><?= e(user()['role'] ?? 'ADMIN') ?></small>
      </div>
      <a href="logout.php" title="Đăng xuất" style="color:#94a3b8">↪</a>
    </div>
  </div>
</aside>
