<?php
$pdo = db();
$customers = $pdo->query('SELECT id,name,phone FROM customers ORDER BY name')->fetchAll();
$vehicles = $pdo->query('SELECT id,brand,model,vin FROM vehicles ORDER BY brand,model')->fetchAll();
$orders = $pdo->query("SELECT r.*, c.name customer_name, v.brand, v.model FROM repair_orders r LEFT JOIN customers c ON c.id=r.customer_id LEFT JOIN vehicles v ON v.id=r.vehicle_id ORDER BY r.id DESC LIMIT 30")->fetchAll();
?>
<div class="page-head">
  <div>
    <p class="muted">Tiếp nhận xe, lập lệnh sửa chữa, phân công kỹ thuật viên, theo dõi phụ tùng và thanh toán</p>
  </div>
  <?php if(can('service')):?>
    <button class="btn primary" onclick="openModal('repairModal')">＋ Lập lệnh sửa chữa mới</button>
  <?php endif;?>
</div>

<div class="kpi-grid compact">
  <div class="stat-card">
    <span>Lệnh đang tiếp nhận</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM repair_orders WHERE status IN ('Mới','Đã tiếp nhận')")->fetchColumn()?></strong>
    <span class="trend neutral">Chờ kiểm tra</span>
  </div>
  <div class="stat-card">
    <span>Đang sửa chữa</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM repair_orders WHERE status='Đang sửa chữa'")->fetchColumn()?></strong>
    <span class="trend up">↗ Kỹ thuật viên làm việc</span>
  </div>
  <div class="stat-card">
    <span>Hoàn tất / Đã bàn giao</span>
    <strong><?=$pdo->query("SELECT COUNT(*) FROM repair_orders WHERE status IN ('Hoàn tất','Đã bàn giao')")->fetchColumn()?></strong>
    <span class="trend up">Đã nghiệm thu</span>
  </div>
  <div class="stat-card">
    <span>Doanh thu dịch vụ</span>
    <strong><?=money($pdo->query("SELECT COALESCE(SUM(total),0) FROM repair_orders")->fetchColumn())?></strong>
    <small>Công & Phụ tùng</small>
  </div>
</div>

<section class="card">
  <div class="section-head">
    <div>
      <h2>Danh sách Phiếu / Lệnh sửa chữa</h2>
      <p>Theo dõi tiến độ quy trình tiếp nhận & sửa chữa</p>
    </div>
    <div class="toolbar-inline">
      <input placeholder="⌕ Tìm mã lệnh RO, tên KH, mẫu xe...">
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mã lệnh (RO)</th>
          <th>Khách hàng</th>
          <th>Xe & Biển số / VIN</th>
          <th>Cố vấn & Kỹ thuật</th>
          <th>Số KM</th>
          <th>Tổng tiền lệnh</th>
          <th>Trạng thái quy trình</th>
          <th>Cập nhật nhanh</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orders as $o):?>
          <tr>
            <td><b class="blue"><?=e($o['code'])?></b></td>
            <td><b><?=e($o['customer_name'] ?? '—')?></b></td>
            <td><?=e(($o['brand']??'AutoHub').' '.($o['model']??''))?></td>
            <td><small>CV: <?=e($o['advisor'])?><br>KTV: <?=e($o['technician'])?></small></td>
            <td><?=number_format($o['mileage'],0,',','.')?> km</td>
            <td><b><?=money($o['total'])?></b></td>
            <td><span class="badge <?=status_class($o['status'])?>"><?=e($o['status'])?></span></td>
            <td>
              <?php if(can('service')):?>
                <form method="post" class="inline-form">
                  <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
                  <input type="hidden" name="action" value="repair_status">
                  <input type="hidden" name="id" value="<?=$o['id']?>">
                  <select name="status" onchange="this.form.submit()">
                    <option <?= $o['status']==='Mới'?'selected':''?>>Mới</option>
                    <option <?= $o['status']==='Đã tiếp nhận'?'selected':''?>>Đã tiếp nhận</option>
                    <option <?= $o['status']==='Đang kiểm tra'?'selected':''?>>Đang kiểm tra</option>
                    <option <?= $o['status']==='Đang sửa chữa'?'selected':''?>>Đang sửa chữa</option>
                    <option <?= $o['status']==='Chờ phụ tùng'?'selected':''?>>Chờ phụ tùng</option>
                    <option <?= $o['status']==='Hoàn tất'?'selected':''?>>Hoàn tất</option>
                    <option <?= $o['status']==='Đã bàn giao'?'selected':''?>>Đã bàn giao</option>
                  </select>
                </form>
              <?php else: ?>
                <span>—</span>
              <?php endif;?>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if (!$orders): ?>
          <tr><td colspan="8" class="empty-state">Chưa có lệnh sửa chữa nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Modal Tạo lệnh sửa chữa wide -->
<div id="repairModal" class="modal-backdrop hidden">
  <form class="modal wide-modal" method="post" onsubmit="return prepareRepairSubmit()">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="repair_create">
    <div class="modal-head">
      <div>
        <h2>Tiếp nhận xe & Lập lệnh sửa chữa (Repair Order)</h2>
        <p>Ghi nhận tình trạng xe, cố vấn dịch vụ và các hạng mục công việc / phụ tùng</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('repairModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Khách hàng *
        <select name="customer_id" required>
          <?php foreach($customers as $c):?>
            <option value="<?=$c['id']?>"><?=e($c['name'])?> — <?=e($c['phone'])?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Xe sửa chữa *
        <select name="vehicle_id" required>
          <?php foreach($vehicles as $v):?>
            <option value="<?=$v['id']?>"><?=e($v['brand'].' '.$v['model'])?> — VIN: <?=e($v['vin'])?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Cố vấn dịch vụ phụ trách<input name="advisor" value="Nguyễn Thành Đạt"></label>
      <label>Kỹ thuật viên thực hiện<input name="technician" value="Trần Quốc Bình"></label>
      <label>Số KM hiện tại<input name="mileage" type="number" value="15000"></label>
      <label>Mức giảm giá (₫)<input id="repairDiscount" name="discount" type="number" value="0" oninput="calcRepairTotal()"></label>
      <label class="full">Mô tả hiện trạng & Yêu cầu của khách<textarea name="description">Bảo dưỡng định kỳ, kiểm tra hệ thống phanh và thay dầu nhớt.</textarea></label>
    </div>

    <div class="section-head modal-section">
      <div>
        <h3>Chi tiết Công việc & Phụ tùng sử dụng</h3>
        <p>Thêm các dòng dịch vụ hoặc vật tư phụ tùng thay thế</p>
      </div>
      <button type="button" class="btn secondary" onclick="addRepairRow()">＋ Thêm hạng mục</button>
    </div>

    <div class="table-wrap form-table">
      <table id="repairItems">
        <thead>
          <tr>
            <th>Loại</th>
            <th>Mã hạng mục</th>
            <th>Tên công việc / Phụ tùng</th>
            <th>SL</th>
            <th>Đơn giá (₫)</th>
            <th>Thành tiền</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><select name="items[0][type]"><option>Công việc</option><option>Phụ tùng</option></select></td>
            <td><input name="items[0][code]" value="CV-BD-15K"></td>
            <td><input name="items[0][name]" value="Công bảo dưỡng định kỳ 15.000km" required></td>
            <td><input name="items[0][qty]" type="number" value="1" min="1" oninput="calcRepairTotal()"></td>
            <td><input name="items[0][unit_price]" type="number" value="1500000" min="0" oninput="calcRepairTotal()"></td>
            <td class="row-total">1.500.000 ₫</td>
            <td><button type="button" class="icon-btn" onclick="this.closest('tr').remove();calcRepairTotal()">×</button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="repair-summary">
      <div><span>Tạm tính công & vật tư:</span><b id="repairSubtotal">1.500.000 ₫</b></div>
      <div><span>Thuế VAT 10%:</span><b id="repairTax">150.000 ₫</b></div>
      <div><span>Chiết khấu giảm giá:</span><b id="repairDiscountView">0 ₫</b></div>
      <div class="grand"><span>TỔNG CỘNG LỆNH SỬA CHỮA:</span><strong id="repairTotal">1.650.000 ₫</strong></div>
    </div>

    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('repairModal')">Hủy</button>
      <button class="btn primary">Lưu lệnh sửa chữa</button>
    </div>
  </form>
</div>
