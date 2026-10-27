<?php
$pdo = db();
$stages = ['Khách hàng mới', 'Đã liên hệ / lái thử', 'Đang thương lượng', 'Đã chốt'];
$opps = $pdo->query("SELECT o.*, c.name customer_name, c.phone customer_phone, v.brand, v.model vehicle_model, v.price vehicle_price, e.name employee_name FROM sales_opportunities o LEFT JOIN customers c ON c.id=o.customer_id LEFT JOIN vehicles v ON v.id=o.vehicle_id LEFT JOIN employees e ON e.id=o.employee_id ORDER BY o.id DESC")->fetchAll();
$customers = $pdo->query('SELECT id,name,phone FROM customers ORDER BY name')->fetchAll();
$employees = $pdo->query('SELECT id,name FROM employees ORDER BY name')->fetchAll();
$vehicles = $pdo->query("SELECT id,brand,model,price FROM vehicles WHERE status IN ('Có sẵn','Đã giữ chỗ') ORDER BY brand,model")->fetchAll();
$testDrives = $pdo->query("SELECT t.*, c.name customer_name, v.brand, v.model vehicle_model FROM test_drives t LEFT JOIN customers c ON c.id=t.customer_id LEFT JOIN vehicles v ON v.id=t.vehicle_id ORDER BY t.id DESC")->fetchAll();
?>
<div class="page-head">
  <div>
    <p class="muted">Quản lý Lead, Cơ hội bán hàng Kanban, Lịch lái thử và Đơn hàng đặt cọc</p>
  </div>
  <div class="head-actions">
    <button class="btn secondary" onclick="openModal('testDriveModal')">◷ Quản lý Lịch lái thử (<?=count($testDrives)?>)</button>
    <button class="btn primary" onclick="openModal('oppModal')">＋ Tạo cơ hội bán hàng</button>
  </div>
</div>

<div class="sales-toolbar">
  <div class="searchbox">
    <span>⌕</span>
    <input id="oppSearch" placeholder="Tìm tên khách hàng hoặc mẫu xe..." oninput="filterOpps()">
  </div>
  <select><option>Tất cả nhân viên tư vấn</option></select>
  <select><option>Tháng 10/2026</option></select>
  <span class="spacer"></span>
  <span class="hint">↕ Pipeline bán hàng Kanban</span>
</div>

<div class="kanban-board">
  <?php foreach($stages as $stage): 
    $items = array_values(array_filter($opps, fn($o) => $o['stage'] === $stage));
  ?>
    <section class="kanban-column" data-stage="<?=e($stage)?>">
      <div class="kanban-head">
        <div>
          <b><?=e($stage)?></b>
          <small><?=count($items)?> cơ hội</small>
        </div>
        <button class="more-btn">•••</button>
      </div>
      <div class="drop-zone" data-stage="<?=e($stage)?>">
        <?php foreach($items as $o):?>
          <article class="opportunity-card" draggable="true" data-id="<?=$o['id']?>" data-search="<?=e(mb_strtolower(($o['customer_name']??'').' '.($o['vehicle_model']??'')))?>" onclick="openOpportunity(<?=$o['id']?>)">
            <div class="opp-card-top">
              <span class="avatar sm"><?=e(initials($o['customer_name']??'KH'))?></span>
              <button class="more-btn" onclick="event.stopPropagation();showToast('info','Cơ hội bán hàng #<?=$o['id']?>')">•••</button>
            </div>
            <b class="opp-name"><?=e($o['customer_name']??'Khách vãng lai')?></b>
            <p><?=e(($o['brand']??'AutoHub').' '.($o['vehicle_model']??''))?></p>
            <strong><?=money($o['value'])?></strong>
            <div class="opp-footer">
              <span class="badge <?=status_class($o['stage'])?>">
                <?=e($o['stage']==='Đã chốt' ? 'Đã đặt cọc' : ($o['stage']==='Đang thương lượng' ? 'Báo giá V2' : 'Lái thử'))?>
              </span>
              <small>▣ <?= $o['follow_up'] ? date('d/m',strtotime($o['follow_up'])) : '—' ?></small>
            </div>
          </article>
        <?php endforeach;?>
      </div>
      <button class="add-opportunity" onclick="openModal('oppModal');event.stopPropagation()">＋ Thêm cơ hội</button>
    </section>
  <?php endforeach; ?>
</div>

<!-- Modal Tạo cơ hội bán hàng -->
<div id="oppModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="sales_create">
    <div class="modal-head">
      <div>
        <h2>Tạo cơ hội bán hàng mới</h2>
        <p>Gán khách hàng, dòng xe quan tâm và nhân viên tư vấn.</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('oppModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Khách hàng *
        <select name="customer_id" required>
          <?php foreach($customers as $c):?>
            <option value="<?=$c['id']?>"><?=e($c['name'])?> — <?=e($c['phone'])?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Xe quan tâm *
        <select name="vehicle_id" required>
          <?php foreach($vehicles as $v):?>
            <option value="<?=$v['id']?>"><?=e($v['brand'].' '.$v['model'].' ('.money($v['price']).')')?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Nhân viên phụ trách
        <select name="employee_id">
          <?php foreach($employees as $e):?>
            <option value="<?=$e['id']?>"><?=e($e['name'])?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Giá trị dự kiến (₫)<input name="value" type="number" value="2490000000"></label>
      <label>Khả năng chốt (%)<input name="probability" type="number" min="0" max="100" value="60"></label>
      <label>Ngày follow-up<input name="follow_up" type="date" value="<?=date('Y-m-d', strtotime('+3 days'))?>"></label>
      <label>Giai đoạn
        <select name="stage">
          <option>Khách hàng mới</option>
          <option>Đã liên hệ / lái thử</option>
          <option>Đang thương lượng</option>
          <option>Đã chốt</option>
        </select>
      </label>
      <label class="full">Ghi chú nhu cầu<textarea name="note" placeholder="Yêu cầu khuyến mãi, giao xe..."></textarea></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('oppModal')">Hủy</button>
      <button class="btn primary">Tạo cơ hội</button>
    </div>
  </form>
</div>

<!-- Modal Chi tiết cơ hội -->
<div id="oppDetailModal" class="modal-backdrop hidden">
  <div class="modal">
    <div class="modal-head">
      <div>
        <h2 id="oppDetailTitle">Chi tiết cơ hội bán hàng</h2>
        <p id="oppDetailSub"></p>
      </div>
      <button class="icon-btn" onclick="closeModal('oppDetailModal')">×</button>
    </div>
    <div class="detail-body" id="oppDetailBody"></div>
    <div class="modal-foot">
      <button class="btn secondary" onclick="closeModal('oppDetailModal')">Đóng</button>
      <button class="btn primary" onclick="closeModal('oppDetailModal');showToast('success','Đã chuyển sang phiếu thu tiền cọc xe.')">Đặt cọc xe</button>
    </div>
  </div>
</div>

<!-- Modal Lịch lái thử -->
<div id="testDriveModal" class="modal-backdrop hidden">
  <div class="modal wide-modal">
    <div class="modal-head">
      <div>
        <h2>Danh sách Lịch lái thử xe</h2>
        <p>Điều phối lịch lái thử cho khách hàng</p>
      </div>
      <button class="icon-btn" onclick="closeModal('testDriveModal')">×</button>
    </div>
    <div class="table-wrap" style="padding:15px 20px">
      <table>
        <thead>
          <tr>
            <th>Ngày & Giờ</th>
            <th>Khách hàng</th>
            <th>Mẫu xe lái thử</th>
            <th>Trạng thái</th>
            <th>Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($testDrives as $td): ?>
            <tr>
              <td><b><?=date('d/m/Y', strtotime($td['drive_date']))?></b> <?=substr($td['drive_time'],0,5)?></td>
              <td><?=e($td['customer_name'] ?? 'Khách lái thử')?></td>
              <td><b><?=e(($td['brand']??'AutoHub').' '.($td['vehicle_model']??''))?></b></td>
              <td><span class="badge <?=status_class($td['status']==='Đã hoàn tất'?'green':'blue')?>"><?=e($td['status'])?></span></td>
              <td><?=e($td['notes'] ?: 'Chạy thử tuyến showroom')?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="modal-foot">
      <button class="btn secondary" onclick="closeModal('testDriveModal')">Đóng</button>
    </div>
  </div>
</div>

<script>
const oppData = <?=json_encode($opps, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;
function filterOpps(){
    const q = (document.getElementById('oppSearch').value || '').toLowerCase();
    document.querySelectorAll('.opportunity-card').forEach(c => {
        c.style.display = c.dataset.search.includes(q) ? '' : 'none';
    });
}
function openOpportunity(id){
    const o = oppData.find(x => Number(x.id) === Number(id));
    if(!o) return;
    document.getElementById('oppDetailTitle').textContent = o.customer_name || 'Khách hàng';
    document.getElementById('oppDetailSub').textContent = (o.brand||'')+' '+(o.vehicle_model||'')+' • '+(o.customer_phone||'');
    document.getElementById('oppDetailBody').innerHTML = `
        <div class="detail-summary">
            <div><span>Giá trị dự kiến</span><b>${new Intl.NumberFormat('vi-VN').format(o.value)} ₫</b></div>
            <div><span>Tư vấn viên</span><b>${o.employee_name || 'Nguyễn Văn Nam'}</b></div>
            <div><span>Giai đoạn</span><b>${o.stage}</b></div>
            <div><span>Ngày hẹn follow-up</span><b>${o.follow_up || '—'}</b></div>
        </div>
        <div class="detail-actions-grid">
            <button class="btn secondary" onclick="showToast('info','Đã sắp xếp lịch lái thử.')">◷ Lịch lái thử</button>
            <button class="btn secondary" onclick="showToast('info','Đã gửi hợp đồng báo giá qua email.')">▤ Gửi báo giá</button>
            <button class="btn primary" onclick="showToast('success','Đang tạo hóa đơn cọc xe.')">₫ Thu cọc xe</button>
        </div>
    `;
    openModal('oppDetailModal');
}
</script>
