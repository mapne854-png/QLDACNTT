<?php
$pdo = db();
$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM vehicles WHERE 1=1';
$p = [];

if ($search !== '') {
    $sql .= ' AND (brand LIKE ? OR model LIKE ? OR vin LIKE ? OR color LIKE ? OR code LIKE ?)';
    $p = ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"];
}
$sql .= ' ORDER BY id DESC';
$s = $pdo->prepare($sql);
$s->execute($p);
$vehicles = $s->fetchAll();

$availCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Có sẵn'")->fetchColumn();
$heldCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Đã giữ chỗ'")->fetchColumn();
$transitCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Đang vận chuyển'")->fetchColumn();
$soldCount = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='Đã bán'")->fetchColumn();
?>
<div class="page-head">
  <div>
    <p class="muted">Quản lý kho xe, thông số kỹ thuật, mã VIN, giá bán niêm yết, vị trí bãi kho và trạng thái</p>
  </div>
  <?php if(can('vehicle')):?>
    <button class="btn primary" onclick="openModal('vehicleModal')">＋ Nhập xe vào kho</button>
  <?php endif;?>
</div>

<!-- Stat KPI Cards -->
<div class="kpi-grid compact">
  <div class="stat-card">
    <div class="stat-top"><span>Tổng xe quản lý</span><span class="stat-icon blue">▰</span></div>
    <strong><?=count($vehicles)?> <small>xe</small></strong>
    <span class="trend up">Đầy đủ hồ sơ VIN</span>
  </div>
  <div class="stat-card">
    <div class="stat-top"><span>Xe có sẵn trong kho</span><span class="stat-icon green">✓</span></div>
    <strong><?=$availCount?> <small>xe</small></strong>
    <span class="trend up">Sẵn sàng bàn giao</span>
  </div>
  <div class="stat-card">
    <div class="stat-top"><span>Xe đã giữ chỗ / Cọc</span><span class="stat-icon amber">◷</span></div>
    <strong><?=$heldCount?> <small>xe</small></strong>
    <span class="trend neutral">Đang làm hợp đồng</span>
  </div>
  <div class="stat-card">
    <div class="stat-top"><span>Đang vận chuyển (Logistics)</span><span class="stat-icon violet">🚚</span></div>
    <strong><?=$transitCount?> <small>xe</small></strong>
    <span class="trend neutral">Theo dõi lộ trình</span>
  </div>
</div>

<div class="inventory-layout">
  <!-- Bộ lọc sidebar -->
  <aside class="filter-panel card">
    <div class="section-head">
      <div><h2>Bộ lọc kho xe</h2></div>
      <a href="index.php?page=vehicles" class="btn tiny ghost">Đặt lại</a>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
      <div>
        <b style="display:block;font-size:12px;margin-bottom:8px">Thương hiệu / Hãng xe</b>
        <label class="check" style="display:flex;align-items:center;gap:8px;font-size:12px">
          <input type="checkbox" checked disabled> AutoHub Dealership
        </label>
      </div>

      <div>
        <b style="display:block;font-size:12px;margin-bottom:8px">Trạng thái lưu kho</b>
        <div style="display:flex;flex-direction:column;gap:6px;font-size:12px">
          <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" checked> Có sẵn (<?=$availCount?>)</label>
          <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" checked> Đã giữ chỗ (<?=$heldCount?>)</label>
          <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" checked> Đang vận chuyển (<?=$transitCount?>)</label>
          <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" checked> Đã bán (<?=$soldCount?>)</label>
        </div>
      </div>

      <div>
        <b style="display:block;font-size:12px;margin-bottom:6px">Khoảng giá niêm yết</b>
        <input type="range" min="500000000" max="4000000000" step="100000000" value="2500000000" oninput="document.getElementById('priceLabel').textContent=moneyV(this.value)">
        <div style="font-size:11px;color:#2563eb;font-weight:700;margin-top:4px" id="priceLabel">2.500.000.000 ₫</div>
      </div>
    </div>
  </aside>

  <!-- Vehicle Grid -->
  <section>
    <form class="toolbar" method="get">
      <input type="hidden" name="page" value="vehicles">
      <div class="searchbox">
        <span style="color:#94a3b8">⌕</span>
        <input name="q" value="<?=e($search)?>" placeholder="Tìm tên mẫu xe, số khung VIN, màu sắc, vị trí kho...">
      </div>
      <select style="width:160px">
        <option>Mới nhập kho</option>
        <option>Giá tăng dần</option>
        <option>Giá giảm dần</option>
      </select>
      <span class="spacer"></span>
      <button type="button" class="btn secondary" onclick="window.print()">⇩ Xuất hồ sơ kho</button>
    </form>

    <div class="vehicle-grid" id="vehicleGrid">
      <?php foreach($vehicles as $v):
        $hasImg = !empty($v['image_url']);
      ?>
        <article class="vehicle-card" onclick="openVehicle(<?=$v['id']?>)" style="cursor:pointer">
          <div class="vehicle-visual" style="<?=$hasImg ? "background-image:url('".e($v['image_url'])."');background-size:cover;background-position:center;" : ''?>">
            <span class="badge <?=status_class($v['status'])?>"><?=e($v['status'])?></span>
            <?php if(!$hasImg): ?>
              <div class="car-silhouette">
                <span><?=e(strtoupper(substr($v['brand'],0,2)))?></span>
              </div>
            <?php endif; ?>
          </div>
          <div class="vehicle-content">
            <div class="vehicle-title">
              <div>
                <h3><?=e($v['brand'].' '.$v['model'])?></h3>
                <small>VIN: <b><?=e($v['vin'])?></b></small>
              </div>
            </div>
            <div class="vehicle-meta">
              <span class="badge gray">● <?=e($v['color'])?></span>
              <span class="badge gray">Đời <?=$v['year']?></span>
              <span class="badge gray"><?=e($v['location'] ?: 'Kho A')?></span>
            </div>
            <div class="vehicle-price">
              <b><?=money($v['price'])?></b>
              <button class="btn tiny primary" onclick="event.stopPropagation();openVehicle(<?=$v['id']?>)">Chi tiết xe</button>
            </div>
          </div>
        </article>
      <?php endforeach;?>
      <?php if (!$vehicles): ?>
        <div class="empty-state" style="grid-column:1/-1">Không tìm thấy mẫu xe nào phù hợp.</div>
      <?php endif; ?>
    </div>
  </section>
</div>

<!-- Modal Nhập xe vào kho -->
<div id="vehicleModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="vehicle_create">
    <div class="modal-head">
      <div>
        <h2>Nhập xe mới vào kho</h2>
        <p>Khai báo hồ sơ xe, mã VIN và thông số giá bán</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('vehicleModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Hãng sản xuất *<input name="brand" value="AutoHub" required></label>
      <label>Model xe *<input name="model" required placeholder="VD: Luxe Sedan V8"></label>
      <label>Phiên bản<input name="version" placeholder="VD: Presidential / Ultra"></label>
      <label>Năm sản xuất<input type="number" name="year" value="2026"></label>
      <label>Mã số khung (VIN) *<input name="vin" required placeholder="VN8839201920xxx"></label>
      <label>Màu ngoại thất<input name="color" value="Đen Obsidian"></label>
      <label>Giá bán niêm yết (₫) *<input type="number" name="price" value="2490000000" required></label>
      <label>Giá vốn nhập (₫)<input type="number" name="cost_price" value="1950000000"></label>
      <label>Trạng thái
        <select name="status">
          <option>Có sẵn</option>
          <option>Đã giữ chỗ</option>
          <option>Đang vận chuyển</option>
        </select>
      </label>
      <label>Vị trí bãi kho<input name="location" value="Showroom chính - Khu A"></label>
      <label class="full">Đường dẫn ảnh xe (URL hình ảnh)<input name="image_url" placeholder="https://example.com/car-photo.jpg"></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('vehicleModal')">Hủy</button>
      <button class="btn primary">Nhập kho xe</button>
    </div>
  </form>
</div>

<!-- Modal Chi tiết xe Đẹp & Sang trọng (Spacious Vehicle Detail Drawer) -->
<div id="vehicleDetailModal" class="modal-backdrop hidden">
  <div class="modal wide-modal">
    <div class="modal-head">
      <div>
        <h2 id="vehicleName" style="font-size:20px;font-weight:800;color:#0f172a">Chi tiết xe</h2>
        <p id="vehicleSub" style="font-size:12px;color:#64748b;margin-top:4px"></p>
      </div>
      <button class="icon-btn" onclick="closeModal('vehicleDetailModal')">×</button>
    </div>

    <!-- Container chi tiết với Layout 2 cột đẹp mắt -->
    <div id="vehicleDetailBody" style="padding:24px"></div>

    <div class="modal-foot" style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 24px">
      <form method="post" style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:12px">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <input type="hidden" name="action" value="vehicle_status">
        <input type="hidden" id="vehicleId" name="id">

        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:12px;font-weight:600;color:#475569">Cập nhật trạng thái:</span>
          <select name="status" id="vehicleStatusSelect" style="width:200px;padding:8px 12px;font-size:12px">
            <option value="Có sẵn">Có sẵn</option>
            <option value="Đã giữ chỗ">Đã giữ chỗ</option>
            <option value="Đã bán">Đã bán</option>
            <option value="Đang vận chuyển">Đang vận chuyển</option>
          </select>
        </div>

        <div style="display:flex;gap:10px">
          <button class="btn secondary" type="button" onclick="closeModal('vehicleDetailModal')">Đóng</button>
          <button class="btn primary">Cập nhật thay đổi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const vehicleData = <?=json_encode($vehicles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;

function openVehicle(id) {
    const v = vehicleData.find(x => Number(x.id) === Number(id));
    if (!v) return;

    document.getElementById('vehicleName').textContent = v.brand + ' ' + v.model + (v.version ? ' (' + v.version + ')' : '');
    document.getElementById('vehicleSub').textContent = 'Mã quản lý: ' + (v.code || ('XE-'+v.id)) + ' • VIN: ' + v.vin + ' • Đời ' + v.year + ' • Màu ' + v.color;
    document.getElementById('vehicleId').value = v.id;
    
    const sel = document.getElementById('vehicleStatusSelect');
    if (sel) sel.value = v.status;

    const hasImg = Boolean(v.image_url);
    const profit = Math.max(0, Number(v.price || 0) - Number(v.cost_price || 0));

    document.getElementById('vehicleDetailBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:start">
            <!-- Cột trái: Banner ảnh xe & Thông số giá -->
            <div>
                <div style="height:220px;border-radius:12px;overflow:hidden;background:linear-gradient(135deg,#0f172a,#1e293b);display:grid;place-items:center;position:relative;margin-bottom:18px;border:1px solid #e2e8f0;${hasImg ? "background-image:url('"+v.image_url+"');background-size:cover;background-position:center;" : ''}">
                    ${!hasImg ? `<div class="car-silhouette" style="transform:scale(1.2)"><span>${v.brand.substring(0,2).toUpperCase()}</span></div>` : ''}
                    <span class="badge ${getStatusClass(v.status)}" style="position:absolute;top:14px;right:14px;font-size:12px;padding:6px 14px">${v.status}</span>
                </div>

                <!-- Thẻ giá bán & lợi nhuận -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div style="border-right:1px solid #e2e8f0;padding-right:10px">
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;margin-bottom:4px">GIÁ BÁN NIÊM YẾT</span>
                        <b style="font-size:20px;color:#2563eb;font-weight:800">${moneyV(v.price)}</b>
                    </div>
                    <div>
                        <span style="font-size:11px;color:#64748b;font-weight:600;display:block;margin-bottom:4px">GIÁ VỐN NHẬP KHO</span>
                        <b style="font-size:18px;color:#0f172a;font-weight:700">${moneyV(v.cost_price || 0)}</b>
                    </div>
                    <div style="grid-column:1/-1;border-top:1px solid #e2e8f0;padding-top:10px;display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:11px;color:#64748b">Lợi nhuận gộp dự kiến:</span>
                        <b style="font-size:14px;color:#16a34a">${moneyV(profit)}</b>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông số kỹ thuật & Chi tiết quản lý kho -->
            <div style="display:flex;flex-direction:column;gap:16px">
                <!-- Thông số lưu kho -->
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:18px">
                    <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f1f5f9">📋 Thông tin Quản lý Bãi kho</h3>
                    <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                        <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Mã định danh xe:</span><b style="color:#0f172a">${v.code || ('XE-'+v.id)}</b></div>
                        <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Số khung VIN:</span><b style="color:#2563eb">${v.vin}</b></div>
                        <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Vị trí lưu kho:</span><b style="color:#0f172a">${v.location || 'Showroom Khu A'}</b></div>
                        <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Màu sắc ngoại thất:</span><b style="color:#0f172a">${v.color}</b></div>
                        <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Năm sản xuất:</span><b style="color:#0f172a">${v.year}</b></div>
                    </div>
                </div>

                <!-- Thông số kỹ thuật xe -->
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:18px">
                    <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #f1f5f9">⚙ Thông số Kỹ thuật Chi tiết</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px">
                        <div style="background:#f8fafc;padding:8px 10px;border-radius:6px">
                            <span style="color:#94a3b8;display:block;font-size:10px">ĐỘNG CƠ / DUNG TÍCH</span>
                            <b style="color:#0f172a;font-size:12px">${v.model.includes('EV') ? 'Động cơ Điện Dual-Motor' : 'V8 Turbocharged 4.0L'}</b>
                        </div>
                        <div style="background:#f8fafc;padding:8px 10px;border-radius:6px">
                            <span style="color:#94a3b8;display:block;font-size:10px">HỘP SỐ</span>
                            <b style="color:#0f172a;font-size:12px">${v.model.includes('EV') ? 'Tự động 1 cấp EV' : 'Tự động 8 cấp Steptronic'}</b>
                        </div>
                        <div style="background:#f8fafc;padding:8px 10px;border-radius:6px">
                            <span style="color:#94a3b8;display:block;font-size:10px">SỐ CHỖ NGỒI</span>
                            <b style="color:#0f172a;font-size:12px">${v.model.includes('7-Seat') ? '7 Chỗ cao cấp' : '5 Chỗ thể thao'}</b>
                        </div>
                        <div style="background:#f8fafc;padding:8px 10px;border-radius:6px">
                            <span style="color:#94a3b8;display:block;font-size:10px">BẢO HÀNH CHÍNH HÃNG</span>
                            <b style="color:#16a34a;font-size:12px">5 Năm / 150.000km</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    openModal('vehicleDetailModal');
}

function getStatusClass(st) {
    if (st === 'Có sẵn') return 'green';
    if (st === 'Đã giữ chỗ') return 'amber';
    if (st === 'Đang vận chuyển') return 'blue';
    return 'gray';
}
</script>
