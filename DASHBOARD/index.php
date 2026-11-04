<?php
$pdo = db();
$customerCount = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$vehicleAvailable = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Có sẵn'")->fetchColumn();
$soldVehicles = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Đã bán'")->fetchColumn();
$testDriveCount = (int)$pdo->query("SELECT COUNT(*) FROM test_drives WHERE drive_date >= CURDATE()")->fetchColumn();
$monthRevenue = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE payment_status='Đã thanh toán'")->fetchColumn();
$totalLeads = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE status IN ('Mới','Đang tư vấn')")->fetchColumn();
$stockValue = (float)$pdo->query("SELECT COALESCE(SUM(stock*price),0) FROM parts")->fetchColumn();
$activities = $pdo->query('SELECT * FROM activities ORDER BY id DESC LIMIT 5')->fetchAll();
$lowParts = (int)$pdo->query('SELECT COUNT(*) FROM parts WHERE stock <= min_stock')->fetchColumn();
?>
<div class="page-head">
  <div>
    <p class="muted"><?=date('l, d/m/Y')?> • Tổng quan chỉ số kinh doanh & vận hành hệ thống đại lý</p>
  </div>
  <div class="head-actions">
    <button class="btn secondary" onclick="window.print()">⇩ Xuất Báo cáo</button>
    <button class="btn primary" onclick="location.reload()">↻ Làm mới dữ liệu</button>
  </div>
</div>

<div class="kpi-grid">
  <div class="stat-card">
    <div class="stat-top">
      <span>Doanh thu thực thu</span>
      <span class="stat-icon blue">₫</span>
    </div>
    <strong><?=money($monthRevenue)?></strong>
    <span class="trend up">↑ 18.2%</span> <small>so với tháng trước</small>
    <div class="spark">
      <span style="height:35%"></span><span style="height:55%"></span><span style="height:45%"></span>
      <span style="height:75%"></span><span style="height:60%"></span><span style="height:95%"></span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <span>Xe đã bán / Tồn kho</span>
      <span class="stat-icon violet">▰</span>
    </div>
    <strong><?=$soldVehicles?> <small>đã bán</small> / <?=$vehicleAvailable?> <small>tồn</small></strong>
    <span class="trend up">↑ 12.5%</span> <small>Tốc độ luân chuyển kho tốt</small>
    <div class="spark">
      <span style="height:45%"></span><span style="height:40%"></span><span style="height:65%"></span>
      <span style="height:70%"></span><span style="height:68%"></span><span style="height:90%"></span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <span>Lịch lái thử xe</span>
      <span class="stat-icon amber">◷</span>
    </div>
    <strong><?=$testDriveCount?> <small>lịch hẹn</small></strong>
    <span class="trend up">↑ 8.4%</span> <small>Sẵn sàng trải nghiệm</small>
    <div class="spark">
      <span style="height:60%"></span><span style="height:45%"></span><span style="height:80%"></span>
      <span style="height:60%"></span><span style="height:75%"></span><span style="height:85%"></span>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-top">
      <span>Khách hàng tiềm năng</span>
      <span class="stat-icon green">♙</span>
    </div>
    <strong><?=$totalLeads?> <small>leads</small></strong>
    <span class="trend up">↑ 23.1%</span> <small>Từ kênh CRM</small>
    <div class="spark">
      <span style="height:35%"></span><span style="height:55%"></span><span style="height:50%"></span>
      <span style="height:70%"></span><span style="height:80%"></span><span style="height:95%"></span>
    </div>
  </div>
</div>

<div class="dashboard-grid">
  <section class="card">
    <div class="section-head">
      <div>
        <h2>Hiệu suất Bán hàng & Doanh số theo tháng</h2>
        <p>Doanh số thực tế so với chỉ tiêu năm 2026</p>
      </div>
      <select style="width:auto"><option>Năm 2026</option></select>
    </div>

    <div class="legend">
      <span><i class="legend-blue"></i>Doanh số bán xe</span>
      <span><i class="legend-line"></i>Chỉ tiêu Target</span>
    </div>

    <div class="combo-chart">
      <div class="y-axis">
        <span>4 TỶ</span><span>3 TỶ</span><span>2 TỶ</span><span>1 TỶ</span><span>0</span>
      </div>
      <div class="chart-area">
        <div class="target-line" style="bottom:75%"></div>
        <div class="chart-bars">
          <?php foreach([34,45,39,56,48,64,71,60,82,74,68,58] as $i=>$v):?>
            <div class="bar-group">
              <div class="bar-main" style="height:<?=$v*2.3?>px"><span><?=round($v*45)?> tr</span></div>
              <small>T<?=$i+1?></small>
            </div>
          <?php endforeach;?>
        </div>
      </div>
    </div>
  </section>

  <section class="card">
    <div class="section-head">
      <div>
        <h2>Mục tiêu tháng 10/2026</h2>
        <p>Tiến độ chi nhánh AutoHub Sài Gòn</p>
      </div>
    </div>

    <div class="goal-number">
      <?= $monthRevenue > 0 ? number_format(min(100, $monthRevenue/14000000000*100), 1, ',', '.') : '0,0' ?>%
      <span>↑ 4.8%</span>
    </div>

    <div class="progress thick">
      <span style="width:<?=min(100, $monthRevenue/14000000000*100)?>%"></span>
    </div>

    <div class="goal-kv">
      <span>Doanh thu hiện tại</span><b><?=money($monthRevenue)?></b>
      <span>Mục tiêu doanh thu</span><b>14,0 tỷ ₫</b>
      <span>Xe đã hoàn tất bán</span><b><?=$soldVehicles?> xe</b>
    </div>

    <div class="mini-callout">
      <b><?=money(max(0, 14000000000 - $monthRevenue))?></b>
      <span>cần đạt thêm để hoàn thành chỉ tiêu tháng.</span>
    </div>
  </section>
</div>

<div class="dashboard-grid lower-grid">
  <section class="card">
    <div class="section-head">
      <div>
        <h2>Hoạt động gần đây</h2>
        <p>Nhật ký hệ thống thời gian thực</p>
      </div>
      <a href="index.php?page=customers" class="btn tiny ghost">Xem tất cả →</a>
    </div>

    <div class="timeline-list">
      <?php foreach($activities as $a):?>
        <div class="timeline-item">
          <span class="timeline-dot <?=$a['type'] === 'deposit' ? 'green' : ''?>"></span>
          <div>
            <b style="font-size:13px"><?=e($a['title'])?></b>
            <p style="font-size:12px;color:#64748b;margin:2px 0"><?=e($a['description'])?></p>
            <small style="color:#94a3b8;font-size:11px"><?=date('H:i d/m/Y', strtotime($a['created_at']))?> • <?=e($a['created_by'])?></small>
          </div>
        </div>
      <?php endforeach;?>
    </div>
  </section>

  <section class="card">
    <div class="section-head">
      <div>
        <h2>Tình hình Kho & Phụ tùng</h2>
        <p>Tài sản kho bãi và cảnh báo vật tư</p>
      </div>
    </div>

    <div class="resource-grid">
      <div class="resource-box">
        <span>Xe có sẵn</span>
        <b><?=$vehicleAvailable?></b>
        <small style="color:#94a3b8;font-size:10px">xe trong bãi</small>
      </div>
      <div class="resource-box">
        <span>Phụ tùng tồn</span>
        <b><?=$pdo->query('SELECT COALESCE(SUM(stock),0) FROM parts')->fetchColumn()?></b>
        <small style="color:#94a3b8;font-size:10px">sản phẩm</small>
      </div>
      <div class="resource-box">
        <span>Giá trị kho</span>
        <b style="font-size:14px"><?=money($stockValue)?></b>
        <small style="color:#94a3b8;font-size:10px">tổng tài sản</small>
      </div>
    </div>

    <?php if ($lowParts > 0): ?>
      <div class="alert-banner">
        <div class="alert-icon">!</div>
        <div style="flex:1">
          <b style="font-size:12px"><?=$lowParts?> mã phụ tùng sắp hết hàng</b>
          <p style="font-size:11px;margin-top:2px">Cần lập kế hoạch nhập bổ sung ngay.</p>
        </div>
        <a href="index.php?page=parts" class="btn tiny primary">Xử lý</a>
      </div>
    <?php endif; ?>
  </section>
</div>
