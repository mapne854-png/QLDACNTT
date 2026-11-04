<?php
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM customers WHERE id=?');
$s->execute([$id]);
$customer = $s->fetch();

if (!$customer) {
    echo '<div class="card empty-state"><h3>Không tìm thấy khách hàng</h3><br><a class="btn primary" href="index.php?page=customers">Quay lại danh sách</a></div>';
    return;
}

$v = $pdo->query("SELECT * FROM vehicles WHERE status<>'Đã bán' ORDER BY id LIMIT 1")->fetch();

$opp = $pdo->prepare("SELECT o.*, v.model vehicle_model, v.price vehicle_price FROM sales_opportunities o LEFT JOIN vehicles v ON v.id=o.vehicle_id WHERE o.customer_id=? ORDER BY o.id DESC LIMIT 1");
$opp->execute([$id]);
$op = $opp->fetch();

$actStmt = $pdo->prepare("SELECT * FROM activities WHERE customer_id=? ORDER BY id DESC");
$actStmt->execute([$id]);
$activities = $actStmt->fetchAll();
?>
<div class="detail-head">
  <a href="index.php?page=customers" class="back-link">← Danh sách khách hàng</a>
  <div class="detail-actions">
    <button class="btn secondary" onclick="showToast('info','Đang gọi cuộc gọi tới <?=e($customer['phone'])?>')">☎ Gọi điện</button>
    <button class="btn secondary" onclick="showToast('info','Đã gửi SMS tới <?=e($customer['phone'])?>')">▢ Gửi tin nhắn</button>
    <button class="btn primary" onclick="openModal('appointmentModal')">＋ Tạo lịch lái thử / tư vấn</button>
  </div>
</div>

<section class="profile-card card">
  <div class="profile-main">
    <span class="avatar xl"><?=e(initials($customer['name']))?></span>
    <div>
      <div class="profile-title">
        <h2><?=e($customer['name'])?></h2>
        <span class="badge <?=status_class($customer['status'])?>"><?=e($customer['status'])?></span>
      </div>
      <p><?=e($customer['phone'])?> • <?=e($customer['email'] ?: 'Chưa có email')?></p>
      <small><?=e($customer['address'] ?: 'Chưa cập nhật địa chỉ')?> • Mã khách hàng: <b><?=e($customer['code'])?></b></small>
    </div>
  </div>
  <button class="btn secondary" onclick="showToast('info','Tính năng cập nhật thông tin cá nhân.')">✎ Chỉnh sửa hồ sơ</button>
</section>

<div class="detail-columns">
  <div class="stack">
    <!-- Xe sở hữu / Xe mua -->
    <section class="card">
      <div class="section-head">
        <div>
          <h2>Lịch sử mua xe / Xe đã sở hữu</h2>
          <p>Phương tiện liên kết với khách hàng</p>
        </div>
        <button class="link" onclick="showToast('info','Chọn xe liên kết')">＋ Thêm xe</button>
      </div>
      <?php if($v):?>
        <div class="owned-vehicle">
          <div class="vehicle-thumb">
            <span><?=e(strtoupper(substr($v['brand'],0,2)))?></span>
          </div>
          <div class="grow">
            <h3><?=e($v['brand'].' '.$v['model'])?></h3>
            <p>VIN: <?=e($v['vin'])?> • Đời <?=e($v['year'])?></p>
            <div class="badge green">● Chính hãng</div>
          </div>
          <div>
            <b><?=money($v['price'])?></b>
            <small>Giá niêm yết</small>
          </div>
        </div>
      <?php else: ?>
        <p class="muted">Chưa có thông tin xe sở hữu.</p>
      <?php endif;?>
    </section>

    <!-- Cơ hội bán hàng -->
    <section class="card">
      <div class="section-head">
        <div>
          <h2>Cơ hội bán hàng hiện tại</h2>
          <p><?=e($customer['interested_car'])?> • Phụ trách: <?=e($customer['assigned_to'])?></p>
        </div>
        <span class="badge green">Xác suất <?=e((string)($op['probability'] ?? 80))?>%</span>
      </div>
      <div class="opportunity-block">
        <span>GIÁ TRỊ DỰ KIẾN</span>
        <strong><?=money($op['value'] ?? ($v['price'] ?? 2490000000))?></strong>
        <div class="progress thick"><span style="width:75%"></span></div>
        <div class="kv-grid">
          <span>Giai đoạn</span><b><?=e($op['stage'] ?? 'Đang tư vấn')?></b>
          <span>Ngày giao xe dự kiến</span><b><?=date('d/m/Y', strtotime('+10 days'))?></b>
          <span>Tiền đặt cọc</span><b>200.000.000 ₫</b>
          <span>Hình thức mua</span><b>Trả góp ngân hàng 70%</b>
        </div>
      </div>
    </section>
  </div>

  <!-- Lịch sử liên hệ -->
  <section class="card timeline-card">
    <div class="section-head">
      <div>
        <h2>Lịch sử liên hệ & Tương tác</h2>
        <p>Toàn bộ nhật ký chăm sóc khách hàng</p>
      </div>
      <select><option>Tất cả hoạt động</option></select>
    </div>
    <div class="timeline">
      <?php foreach($activities as $act): ?>
        <div>
          <span class="tl-dot blue"></span>
          <div>
            <b><?=e($act['title'])?></b>
            <small><?=date('d/m/Y • H:i', strtotime($act['created_at']))?></small>
            <p><?=e($act['description'])?></p>
            <em><?=e($act['created_by'])?></em>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$activities): ?>
        <div>
          <span class="tl-dot green"></span>
          <div>
            <b>Đã tiếp nhận thông tin KH</b>
            <small><?=date('d/m/Y • H:i', strtotime($customer['created_at']))?></small>
            <p>Khách hàng tìm hiểu về mẫu <?=e($customer['interested_car'])?> từ nguồn <?=e($customer['source'])?>.</p>
            <em><?=e($customer['assigned_to'])?></em>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<!-- Modal Tạo lịch hẹn / Lái thử -->
<div id="appointmentModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="appointment_create">
    <input type="hidden" name="customer_id" value="<?=$customer['id']?>">
    <div class="modal-head">
      <div>
        <h2>Đăng ký lịch lái thử / Tư vấn</h2>
        <p>Tạo lịch hẹn cho khách hàng <?=e($customer['name'])?></p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('appointmentModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Loại dịch vụ
        <select name="type">
          <option>Lái thử</option>
          <option>Tư vấn báo giá</option>
          <option>Bảo dưỡng / Dịch vụ</option>
        </select>
      </label>
      <label>Xe lái thử
        <select name="vehicle_id">
          <?php foreach($pdo->query('SELECT id,brand,model,color FROM vehicles ORDER BY brand,model')->fetchAll() as $vv):?>
            <option value="<?=$vv['id']?>"><?=e($vv['brand'].' '.$vv['model'].' ('.$vv['color'].')')?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Ngày hẹn *<input name="appointment_date" type="date" value="<?=date('Y-m-d', strtotime('+1 day'))?>" required></label>
      <label>Giờ hẹn *<input name="appointment_time" type="time" value="09:30" required></label>
      <label class="full">Nội dung chuẩn bị<textarea name="note" placeholder="Tuyến đường lái thử, giấy tờ mang theo..."></textarea></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('appointmentModal')">Hủy</button>
      <button class="btn primary">Tạo lịch hẹn</button>
    </div>
  </form>
</div>
