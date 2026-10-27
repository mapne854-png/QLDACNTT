<?php
$pdo = db();
$employees = $pdo->query('SELECT * FROM employees ORDER BY result DESC')->fetchAll();
$avg = (float)$pdo->query('SELECT COALESCE(AVG(CASE WHEN target>0 THEN result/target*100 ELSE 0 END),0) FROM employees')->fetchColumn();
$totalTarget = (float)$pdo->query('SELECT COALESCE(SUM(target),0) FROM employees')->fetchColumn();
$totalResult = (float)$pdo->query('SELECT COALESCE(SUM(result),0) FROM employees')->fetchColumn();
?>
<div class="page-head">
  <div>
    <p class="muted">Quản lý hồ sơ nhân sự đại lý, phân công phòng ban, chức vụ và theo dõi chỉ tiêu KPI</p>
  </div>
  <?php if(can('hr')):?>
    <button class="btn primary" onclick="openModal('employeeModal')">＋ Thêm nhân viên</button>
  <?php endif;?>
</div>

<div class="kpi-grid compact">
  <div class="stat-card">
    <span>Tổng số nhân sự</span>
    <strong><?=count($employees)?> <small>thành viên</small></strong>
    <span class="trend up">Đang làm việc</span>
  </div>
  <div class="stat-card">
    <span>Hiệu suất KPI trung bình</span>
    <strong><?=number_format($avg, 1, ',', '.')?>%</strong>
    <span class="trend up">↗ 4,8 điểm</span>
  </div>
  <div class="stat-card">
    <span>Doanh số kết quả thực hiện</span>
    <strong><?=money($totalResult)?></strong>
    <span class="trend up">↗ Tăng trưởng toàn đại lý</span>
  </div>
  <div class="stat-card">
    <span>Tổng chỉ tiêu mục tiêu</span>
    <strong><?=money($totalTarget)?></strong>
    <small>Target đăng ký tháng</small>
  </div>
</div>

<div class="employees-grid">
  <!-- Danh sách nhân viên -->
  <section class="card">
    <div class="section-head">
      <div>
        <h2>Hồ sơ & KPI Nhân viên</h2>
        <p>Danh sách các thành viên phòng Kinh doanh, Dịch vụ, Kỹ thuật, Kế toán, Nhân sự</p>
      </div>
      <input style="max-width:200px" placeholder="⌕ Tìm tên, phòng ban...">
    </div>

    <div class="employee-list">
      <?php foreach($employees as $emp):
        $kpi = $emp['target'] > 0 ? ($emp['result'] / $emp['target']) * 100 : 0;
      ?>
        <article class="employee-item">
          <span class="avatar lg"><?=e(initials($emp['name']))?></span>
          <div class="grow">
            <div class="employee-title">
              <div>
                <b><?=e($emp['name'])?></b>
                <small><?=e($emp['position'])?> • <b><?=e($emp['department'])?></b></small>
              </div>
              <button class="more-btn">•••</button>
            </div>
            <div class="employee-line">
              <span>Mức hoàn thành KPI: <b><?=number_format($kpi, 0)?>%</b></span>
              <span><?=money($emp['result'])?> / <?=money($emp['target'])?></span>
            </div>
            <div class="progress"><span style="width:<?=min(100, $kpi)?>%"></span></div>
          </div>
        </article>
      <?php endforeach;?>
      <?php if (!$employees): ?>
        <div class="empty-state">Chưa có danh sách nhân viên.</div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Biểu đồ KPI -->
  <section class="card chart-card">
    <div class="section-head">
      <div>
        <h2>Xu hướng hoàn thành KPI</h2>
        <p>Tỷ lệ trung bình theo các tháng năm 2026</p>
      </div>
      <select><option>Năm 2026</option></select>
    </div>
    <div class="kpi-big"><?=number_format($avg, 1, ',', '.')?>% <span>↗ 4,8 điểm</span></div>
    <div class="simple-bars">
      <?php foreach([71,74,78,80,76,84,86,89,92,95] as $i=>$x):?>
        <div>
          <span style="height:<?=$x*1.9?>px"></span>
          <small>T<?=$i+1?></small>
        </div>
      <?php endforeach;?>
    </div>
  </section>
</div>

<!-- Modal Thêm nhân viên -->
<div id="employeeModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="employee_create">
    <div class="modal-head">
      <div>
        <h2>Thêm nhân viên mới</h2>
        <p>Khai báo hồ sơ và giao chỉ tiêu KPI tháng</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('employeeModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Họ và tên *<input name="name" required placeholder="VD: Nguyễn Văn B"></label>
      <label>Số điện thoại<input name="phone" placeholder="09xxxxxxxx"></label>
      <label>Email công việc<input type="email" name="email" placeholder="name@autohub.vn"></label>
      <label>Phòng ban
        <select name="department">
          <option>Kinh doanh</option>
          <option>Dịch vụ</option>
          <option>Kỹ thuật</option>
          <option>Kế toán</option>
          <option>Quản lý kho</option>
          <option>Nhân sự</option>
        </select>
      </label>
      <label>Chức vụ<input name="position" value="Tư vấn bán hàng"></label>
      <label>Mức chỉ tiêu KPI Target (₫)<input type="number" name="target" value="3000000000"></label>
      <label>Kết quả đã đạt (₫)<input type="number" name="result" value="0"></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('employeeModal')">Hủy</button>
      <button class="btn primary">Lưu nhân viên</button>
    </div>
  </form>
</div>
