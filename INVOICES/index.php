<?php
$pdo = db();
$customers = $pdo->query('SELECT id,name FROM customers ORDER BY name')->fetchAll();
$invoices = $pdo->query('SELECT i.*, c.name customer_name FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id ORDER BY i.id DESC')->fetchAll();
$payments = $pdo->query('SELECT p.*, i.code invoice_code FROM payments p LEFT JOIN invoices i ON i.id=p.invoice_id ORDER BY p.id DESC LIMIT 10')->fetchAll();

$paid = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE payment_status='Đã thanh toán'")->fetchColumn();
$due = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE payment_status<>'Đã thanh toán'")->fetchColumn();
?>
<div class="page-head">
  <div>
    <p class="muted">Kế toán đại lý, theo dõi hóa đơn bán xe/dịch vụ/phụ tùng, công nợ và phiếu thu chi</p>
  </div>
  <?php if(can('accounting')):?>
    <button class="btn primary" onclick="openModal('invoiceModal')">＋ Lập hóa đơn mới</button>
  <?php endif;?>
</div>

<div class="kpi-grid compact">
  <div class="stat-card">
    <span>Thực thu (Đã thanh toán)</span>
    <strong><?=money($paid)?></strong>
    <span class="trend up">↗ Đã đối soát ngân hàng</span>
  </div>
  <div class="stat-card">
    <span>Công nợ chưa thu</span>
    <strong><?=money($due)?></strong>
    <span class="trend down">Cần nhắc thanh toán</span>
  </div>
  <div class="stat-card">
    <span>Tổng hóa đơn đã xuất</span>
    <strong><?=count($invoices)?> <small>hóa đơn</small></strong>
  </div>
  <div class="stat-card">
    <span>Doanh thu Bán xe / Dịch vụ</span>
    <strong>85% / 15%</strong>
    <small>Cơ cấu doanh số</small>
  </div>
</div>

<section class="card">
  <div class="section-head">
    <div>
      <h2>Danh sách Hóa đơn & Chứng từ kế toán</h2>
      <p>Theo dõi trạng thái thanh toán và công nợ</p>
    </div>
    <div class="toolbar-inline">
      <input id="invoiceSearch" placeholder="⌕ Tìm mã hóa đơn, tên khách hàng..." oninput="filterInvoices()">
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Mã hóa đơn</th>
          <th>Ngày phát hành</th>
          <th>Khách hàng</th>
          <th>Phân loại</th>
          <th>Tổng số tiền</th>
          <th>Trạng thái thanh toán</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody id="invoiceRows">
        <?php foreach($invoices as $i):?>
          <tr data-search="<?=e(mb_strtolower(($i['code']??'').' '.($i['customer_name']??'')))?>">
            <td><b class="blue"><?=e($i['code'])?></b></td>
            <td><?=date('d/m/Y',strtotime($i['issued_at']))?></td>
            <td><b><?=e($i['customer_name']??'—')?></b></td>
            <td><span class="badge gray"><?=e($i['type'])?></span></td>
            <td><b><?=money($i['total'])?></b></td>
            <td><span class="badge <?=status_class($i['payment_status'])?>"><?=e($i['payment_status'])?></span></td>
            <td>
              <button class="btn tiny secondary" onclick="window.print()">⇩ In PDF</button>
              <?php if($i['payment_status']!=='Đã thanh toán' && can('accounting')):?>
                <button class="btn tiny primary" onclick="openPayment(<?=$i['id']?>,'<?=e($i['code'])?>',<?=e((string)$i['total'])?>)">Thu tiền</button>
              <?php endif;?>
            </td>
          </tr>
        <?php endforeach;?>
        <?php if (!$invoices): ?>
          <tr><td colspan="7" class="empty-state">Chưa có hóa đơn nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Modal Tạo hóa đơn -->
<div id="invoiceModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="invoice_create">
    <div class="modal-head">
      <div>
        <h2>Tạo hóa đơn tài chính mới</h2>
        <p>Tính tự động Tạm tính + VAT 10% - Giảm giá</p>
      </div>
      <button type="button" class="icon-btn" onclick="closeModal('invoiceModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Khách hàng *
        <select name="customer_id" required>
          <?php foreach($customers as $c):?>
            <option value="<?=$c['id']?>"><?=e($c['name'])?></option>
          <?php endforeach;?>
        </select>
      </label>
      <label>Loại hóa đơn
        <select name="type">
          <option>Bán xe</option>
          <option>Dịch vụ</option>
          <option>Phụ tùng</option>
        </select>
      </label>
      <label>Tạm tính trước thuế (₫) *<input type="number" name="subtotal" value="100000000" required></label>
      <label>Giảm giá / Chiết khấu (₫)<input type="number" name="discount" value="0"></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('invoiceModal')">Hủy</button>
      <button class="btn primary">Tạo hóa đơn</button>
    </div>
  </form>
</div>

<!-- Modal Ghi nhận Thu tiền / Thanh toán -->
<div id="paymentModal" class="modal-backdrop hidden">
  <form class="modal" method="post">
    <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
    <input type="hidden" name="action" value="invoice_pay">
    <input type="hidden" id="paymentInvoiceId" name="id">
    <div class="modal-head">
      <div>
        <h2>Lập Phiếu thu tiền thanh toán</h2>
        <p id="paymentInvoiceCode"></p>
      </div>
      <button class="icon-btn" type="button" onclick="closeModal('paymentModal')">×</button>
    </div>
    <div class="form-grid">
      <label>Số tiền thu (₫) *<input id="paymentAmount" name="amount" type="number" min="1" required></label>
      <label>Phương thức thanh toán
        <select name="method">
          <option>Chuyển khoản ngân hàng</option>
          <option>Tiền mặt</option>
          <option>Quẹt thẻ POS</option>
        </select>
      </label>
      <label class="full">Mã giao dịch / Mã tham chiếu ngân hàng<input name="reference" placeholder="VD: FT2628109201..."></label>
    </div>
    <div class="modal-foot">
      <button type="button" class="btn secondary" onclick="closeModal('paymentModal')">Hủy</button>
      <button class="btn primary">Xác nhận đã thu tiền</button>
    </div>
  </form>
</div>

<script>
function openPayment(id, code, amount){
    document.getElementById('paymentInvoiceId').value = id;
    document.getElementById('paymentInvoiceCode').textContent = 'Hóa đơn: ' + code;
    document.getElementById('paymentAmount').value = amount;
    openModal('paymentModal');
}
function filterInvoices(){
    const q = (document.getElementById('invoiceSearch').value || '').toLowerCase();
    document.querySelectorAll('#invoiceRows tr').forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? '' : 'none';
    });
}
</script>
