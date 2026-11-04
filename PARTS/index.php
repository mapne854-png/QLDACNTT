<?php
$pdo = db();
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM parts WHERE 1=1';
$params = [];

if ($q !== '') {
    $sql .= ' AND (sku LIKE ? OR name LIKE ? OR category LIKE ?)';
    $params = ["%$q%", "%$q%", "%$q%"];
}

$sql .= ' ORDER BY id DESC';
$s = $pdo->prepare($sql);
$s->execute($params);
$parts = $s->fetchAll();

$low = (int)$pdo->query('SELECT COUNT(*) FROM parts WHERE stock <= min_stock')->fetchColumn();
$value = (float)$pdo->query('SELECT COALESCE(SUM(stock*price),0) FROM parts')->fetchColumn();
?>
<div class="page-head">
  <div>
    <p class="muted">Theo dõi danh mục phụ tùng, tồn kho, xuất nhập kho và cảnh báo tồn tối thiểu</p>
  </div>
  <?php if(can('parts')):?>
    <button class="btn primary" onclick="openModal('partModal')">＋ Thêm mã phụ tùng</button>
  <?php endif;?>
</div>

<?php if($low > 0):?>
  <div class="alert warn">
    <span>!</span>
    <div>
      <b>Cảnh báo tồn kho thấp</b>
      <p>Có <b><?=$low?> mã phụ tùng</b> đang ở dưới hoặc bằng ngưỡng tồn tối thiểu. Hãy điều chỉnh hoặc lập kế hoạch nhập bổ sung.</p>
    </div>
    <a href="#partsTable">Xem danh sách</a>
  </div>
<?php endif; ?>

<div class="kpi-grid compact">
  <div class="stat-card">
    <span>Tổng mã phụ tùng</span>
    <strong><?=count($parts)?> <small>mã hàng</small></strong>
  </div>
  <div class="stat-card">
    <span>Tổng giá trị kho phụ tùng</span>
    <strong><?=money($value)?></strong>
    <span class="trend up">↗ Định giá tài sản</span>
  </div>
  <div class="stat-card">
    <span>Số mã cần nhập bổ sung</span>
    <strong><?=$low?> mã</strong>
    <span class="trend down">Dưới ngưỡng min</span>
  </div>
  <div class="stat-card">
    <span>Đơn vị cung ứng OEM</span>
    <strong>Denso, Michelin, Brembo</strong>
    <small>Chính hãng AutoHub</small>
  </div>
</div>

<section class="card" id="partsTable">
  <form class="toolbar" method="get">
    <input type="hidden" name="page" value="parts">
    <div class="searchbox">
      <span>⌕</span>
      <input name="q" value="<?=e($q)?>" placeholder="Tìm theo SKU, tên phụ tùng, danh mục...">
    </div>
    <select><option>Tất cả danh mục</option><option>Dầu nhớt</option><option>Hệ thống phanh</option><option>Hệ thống lọc</option></select>
    <button class="btn secondary">Lọc</button>
    <span class="spacer"></span>
    <button type="button" class="btn secondary" onclick="window.print()">⇩ Xuất báo cáo kho</button>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mã SKU</th>
          <th>Tên phụ tùng & Nhà cung cấp</th>
          <th>Danh mục</th>
          <th>Tồn hiện tại</th>
          <th>Tồn tối thiểu</th>
          <th>Đơn giá niêm yết</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($parts as $part):
          $isLow = $part['stock'] <= $part['min_stock'];
        ?>
          <tr>
            <td><b class="blue"><?=e($part['sku'])?></b></td>
            <td>
              <div class="part-name">
                <span class="part-icon">◈</span>
                <div>
                  <b><?=e($part['name'])?></b>
                  <small><?=e($part['supplier'] ?: 'AutoHub OEM')?> • Kệ: <?=e($part['location'] ?: 'Kệ A')?></small>
                </div>
              </div>
            </td>
            <td><?=e($part['category'])?></td>
            <td>
              <div class="stock-value">
                <b><?=$part['stock']?> <?=e($part['unit'] ?: 'Cái')?></b>
                <div class="stock-track">
                  <span class="<?=$isLow?'low':''?>" style="width:<?=min(100, max(8, ($part['stock']/max(1,$part['min_stock']*2))*100))?>%"></span>
                </div>
              </div>
            </td>
            <td><?=$part['min_stock']?></td>
            <td><b><?=money($part['price'])?></b></td>
            <td>
              <span class="badge <?=$isLow ? 'red' : 'green'?>">
                <?=$isLow ? 'Cảnh báo tồn thấp' : 'Đủ tồn kho'?>
              </span>
            </td>
            <td>
              <div class="actions">
                <?php if(can('parts')):?>
                  <button class="btn tiny secondary" onclick="openStock(<?=$part['id']?>,'<?=e($part['name'])?>')">Nhập/Xuất kho</button>
                <?php endif;?>
              </div>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if (!$parts): ?>
          <tr><td colspan="8" class="empty-state">Không có mã phụ tùng nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Modal Thêm phụ tùng -->
<div id="partModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="part_create">
    <div class="modal-head">
      <div>
        <h2>Thêm mã phụ tùng mới</h2>
        <p>Khai báo danh mục vật tư phụ tùng</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('partModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Mã SKU *<input name="sku" required placeholder="VD: PT-OIL-5W30"></label>
      <label>Tên phụ tùng *<input name="name" required placeholder="VD: Dầu động cơ 5W-30"></label>
      <label>Danh mục<input name="category" value="Phụ tùng chung"></label>
      <label>Đơn vị tính<input name="unit" value="Cái"></label>
      <label>Số lượng tồn ban đầu<input type="number" name="stock" value="10"></label>
      <label>Ngưỡng tồn tối thiểu<input type="number" name="min_stock" value="5"></label>
      <label>Đơn giá bán (₫)<input type="number" name="price" value="500000"></label>
      <label>Giá vốn nhập (₫)<input type="number" name="cost_price" value="300000"></label>
      <label>Nhà cung cấp<input name="supplier" value="AutoHub OEM Parts"></label>
      <label>Vị trí kệ kho<input name="location" value="Kệ A-01"></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('partModal')">Hủy</button>
      <button class="btn primary">Lưu phụ tùng</button>
    </div>
  </form>
</div>

<!-- Modal Điều chỉnh kho (Nhập/Xuất) -->
<div id="stockModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="part_adjust">
    <input type="hidden" id="stockPartId" name="id">
    <div class="modal-head">
      <div>
        <h2>Phiếu Nhập / Xuất kho phụ tùng</h2>
        <p id="stockPartName"></p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('stockModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Loại giao dịch
        <select name="type">
          <option value="IN">Nhập kho (Thêm tồn)</option>
          <option value="OUT">Xuất kho (Trừ tồn)</option>
        </select>
      </label>
      <label>Số lượng *<input type="number" name="qty" value="1" min="1" required></label>
      <label class="full">Lý do / Số chứng từ phiếu<textarea name="note" placeholder="Nhập kho bổ sung định kỳ / Xuất phục vụ lệnh sửa chữa..."></textarea></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('stockModal')">Hủy</button>
      <button class="btn primary">Cập nhật kho</button>
    </div>
  </form>
</div>

<script>
function openStock(id, name){
    document.getElementById('stockPartId').value = id;
    document.getElementById('stockPartName').textContent = name;
    openModal('stockModal');
}
</script>
