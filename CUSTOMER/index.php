<?php
$pdo = db();
$q = trim($_GET['q'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');

$sql = 'SELECT * FROM customers WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR interested_car LIKE ? OR code LIKE ?)';
    $params = ["%$q%", "%$q%", "%$q%", "%$q%", "%$q%"];
}

if ($filterStatus !== '') {
    $sql .= ' AND status=?';
    $params[] = $filterStatus;
}

$sql .= ' ORDER BY id DESC';
$s = $pdo->prepare($sql);
$s->execute($params);
$customers = $s->fetchAll();
?>
<div class="page-head">
  <div>
    <p class="muted">Quản lý thông tin, nguồn khách hàng, lịch sử liên hệ và mua xe</p>
  </div>
  <?php if(can('crm')):?>
    <button class="btn primary" onclick="openModal('customerModal')"> + Thêm khách hàng</button>
  <?php endif;?>
</div>

<div class="kpi-grid compact">
  <div class="stat-card">
    <span>Tổng khách hàng</span>
    <strong><?=$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn()?></strong>
    <span class="trend up">↗ Hồ sơ lưu trữ</span>
  </div>
  <div class="stat-card">
    <span>Khách hàng mới</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM customers WHERE status='Mới'")->fetchColumn()?></strong>
    <span class="trend up">↗ Cần chăm sóc</span>
  </div>
  <div class="stat-card">
    <span>Đang tư vấn</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM customers WHERE status='Đang tư vấn'")->fetchColumn()?></strong>
    <span class="trend neutral">Đang tương tác</span>
  </div>
  <div class="stat-card">
    <span>Đã mua / Đặt cọc</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('Đã đặt cọc','Đã mua')")->fetchColumn()?></strong>
    <span class="trend up">↗ Chuyển đổi thành công</span>
  </div>
</div>

<section class="card">
  <form class="toolbar" method="get">
    <input type="hidden" name="page" value="customers">
    <div class="searchbox">
      <span>⌕</span>
      <input name="q" value="<?=e($q)?>" placeholder="Tìm theo tên, SĐT, mã KH, mẫu xe...">
    </div>
    <select name="status">
      <option value="">Trạng thái: Tất cả</option>
      <?php foreach(['Mới','Đang tư vấn','Đã đặt cọc','Đã mua'] as $x):?>
        <option value="<?=$x?>" <?= $filterStatus===$x?'selected':'' ?>><?=$x?></option>
      <?php endforeach;?>
    </select>
    <button class="btn secondary">Lọc</button>
    <a class="btn ghost" href="index.php?page=customers">Đặt lại</a>
    <span class="spacer"></span>
    <button type="button" class="btn secondary" onclick="window.print()">⇩ Xuất báo cáo</button>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mã & Khách hàng</th>
          <th>Số điện thoại</th>
          <th>Email</th>
          <th>Xe quan tâm</th>
          <th>Nguồn khách</th>
          <th>Phụ trách</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($customers as $c):?>
          <tr>
            <td>
              <a class="person" href="index.php?page=customer-detail&id=<?=$c['id']?>">
                <span class="avatar sm"><?=e(initials($c['name']))?></span>
                <span>
                  <b><?=e($c['name'])?></b>
                  <small><?=e($c['code'])?></small>
                </span>
              </a>
            </td>
            <td><?=e($c['phone'])?></td>
            <td><?=e($c['email'] ?: '—')?></td>
            <td><b><?=e($c['interested_car'] ?: '—')?></b></td>
            <td><?=e($c['source'])?></td>
            <td><?=e($c['assigned_to'])?></td>
            <td><span class="badge <?=status_class($c['status'])?>"><?=e($c['status'])?></span></td>
            <td><?=date('d/m/Y',strtotime($c['created_at']))?></td>
            <td>
              <div class="actions">
                <a class="icon-btn" href="index.php?page=customer-detail&id=<?=$c['id']?>" title="Xem chi tiết">◉</a>
                <?php if(can('crm')):?>
                  <form method="post" onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?')">
                    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
                    <input type="hidden" name="action" value="customer_delete">
                    <input type="hidden" name="id" value="<?=$c['id']?>">
                    <button class="icon-btn danger-text" title="Xóa">×</button>
                  </form>
                <?php endif;?>
              </div>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if (!$customers): ?>
          <tr><td colspan="9" class="empty-state">Không tìm thấy khách hàng nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Modal Thêm khách hàng -->
<div id="customerModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="customer_create">
    <div class="modal-head">
      <div>
        <h2>Thêm khách hàng mới</h2>
        <p>Nhập hồ sơ khách hàng tiềm năng.</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('customerModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Họ và tên *<input name="name" required placeholder="VD: Nguyễn Văn A"></label>
      <label>Số điện thoại *<input name="phone" required placeholder="09xxxxxxxx"></label>
      <label>Email<input type="email" name="email" placeholder="example@domain.com"></label>
      <label>Mẫu xe quan tâm<input name="interested_car" placeholder="VD: AutoHub Luxe Sedan V8"></label>
      <label>Nguồn khách hàng
        <select name="source">
          <option>Showroom</option>
          <option>Trang web</option>
          <option>Mạng xã hội</option>
          <option>Giới thiệu</option>
          <option>Sự kiện / Triển lãm</option>
        </select>
      </label>
      <label>Trạng thái
        <select name="status">
          <option>Mới</option>
          <option>Đang tư vấn</option>
          <option>Đã đặt cọc</option>
          <option>Đã mua</option>
        </select>
      </label>
      <label>Nhân viên phụ trách<input name="assigned_to" value="<?=e(user()['name'] ?? 'Nguyễn Văn Nam')?>"></label>
      <label>Địa chỉ<input name="address" placeholder="Địa chỉ liên hệ"></label>
      <label class="full">Ghi chú nhu cầu<textarea name="notes" placeholder="Ghi chú chi tiết về ngân sách, màu xe, thời gian lấy xe..."></textarea></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('customerModal')">Hủy</button>
      <button class="btn primary">Lưu khách hàng</button>
    </div>
  </form>
</div>
