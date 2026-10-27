<?php
$pdo = db();
$reportSales = $pdo->query("SELECT v.brand, COUNT(*) total, SUM(v.price) total_val FROM sales_opportunities o LEFT JOIN vehicles v ON v.id=o.vehicle_id WHERE o.stage='Đã chốt' GROUP BY v.brand ORDER BY total DESC")->fetchAll();
$topEmployees = $pdo->query("SELECT name, department, target, result, CASE WHEN target>0 THEN result/target*100 ELSE 0 END kpi FROM employees ORDER BY kpi DESC LIMIT 5")->fetchAll();
$serviceStats = $pdo->query("SELECT status, COUNT(*) count FROM repair_orders GROUP BY status")->fetchAll();
$customerSources = $pdo->query("SELECT source, COUNT(*) count FROM customers GROUP BY source")->fetchAll();
?>
<div class="page-head">
  <div>
    <p class="muted">Báo cáo tổng hợp quản trị: Doanh số, Kho xe, Phụ tùng, Dịch vụ, Khách hàng & Nhân sự</p>
  </div>
  <div class="head-actions">
    <button class="btn secondary" onclick="window.print()">⇩ In / Xuất báo cáo tổng hợp</button>
    <button class="btn primary" onclick="showToast('success','Đã tải dữ liệu phân tích mới nhất.')">↻ Cập nhật dữ liệu</button>
  </div>
</div>

<div class="dashboard-grid">
  <!-- Báo cáo 1: Doanh số theo thương hiệu -->
  <section class="card">
    <div class="section-head">
      <div>
        <h2>1. Báo cáo Doanh số bán xe theo thương hiệu</h2>
        <p>Số xe và tổng trị giá giao dịch đã chốt cọc</p>
      </div>
      <select><option>Tháng 10/2026</option></select>
    </div>
    <?php if($reportSales):?>
      <div class="report-bars">
        <?php $max = max(array_column($reportSales, 'total'));
        foreach($reportSales as $r):?>
          <div>
            <div class="report-label">
              <span><b><?=e($r['brand']??'AutoHub')?></b></span>
              <b><?=$r['total']?> xe • <?=money($r['total_val'])?></b>
            </div>
            <div class="progress"><span style="width:<?=($r['total']/max(1,$max))*100?>%"></span></div>
          </div>
        <?php endforeach;?>
      </div>
    <?php else:?>
      <div class="empty-state">Chưa có dữ liệu chốt sale trong kỳ.</div>
    <?php endif;?>
  </section>

  <!-- Báo cáo 2: Top nhân viên đạt KPI -->
  <section class="card">
    <div class="section-head">
      <div>
        <h2>2. Báo cáo Nhân sự - Top KPI</h2>
        <p>Xếp hạng nhân viên tư vấn xuất sắc nhất</p>
      </div>
    </div>
    <div class="rank-list">
      <?php foreach($topEmployees as $i=>$e):?>
        <div>
          <span class="rank">#<?=$i+1?></span>
          <span class="avatar sm"><?=e(initials($e['name']))?></span>
          <div class="grow">
            <b><?=e($e['name'])?></b>
            <small><?=e($e['department'])?> • <?=money($e['result'])?></small>
          </div>
          <strong><?=number_format((float)$e['kpi'], 1, ',', '.')?>%</strong>
        </div>
      <?php endforeach;?>
    </div>
  </section>
</div>

<div class="dashboard-grid lower-grid">
  <!-- Báo cáo 3: Tiếp nhận dịch vụ -->
  <section class="card">
    <div class="section-head">
      <div>
        <h2>3. Báo cáo Tiến độ dịch vụ & Sửa chữa</h2>
        <p>Phân bổ trạng thái phiếu sửa chữa</p>
      </div>
    </div>
    <div class="report-bars">
      <?php foreach($serviceStats as $st): ?>
        <div>
          <div class="report-label">
            <span>Trạng thái: <b><?=e($st['status'])?></b></span>
            <b><?=$st['count']?> xe</b>
          </div>
          <div class="progress"><span style="width:<?=min(100, $st['count']*25)?>%"></span></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Báo cáo 4: Nguồn khách hàng CRM -->
  <section class="card">
    <div class="section-head">
      <div>
        <h2>4. Báo cáo Nguồn Khách hàng (CRM)</h2>
        <p>Phân tích các kênh thu hút leads</p>
      </div>
    </div>
    <div class="rank-list">
      <?php foreach($customerSources as $cs): ?>
        <div>
          <span class="avatar sm dark">♙</span>
          <div class="grow">
            <b><?=e($cs['source'] ?: 'Không xác định')?></b>
          </div>
          <strong><?=$cs['count']?> khách hàng</strong>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
