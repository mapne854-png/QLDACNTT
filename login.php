<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = db()->prepare("SELECT id, name, email, password_hash, role, branch_id FROM users WHERE email=? AND status='active' LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'branch_id' => $row['branch_id'] === null ? null : (int)$row['branch_id']
        ];
        audit('LOGIN', 'User', $row['id'], null, ['email' => $row['email']]);
        flash('success', 'Chào mừng ' . $row['name'] . ' đăng nhập thành công!');
        redirect('index.php');
    }
    $error = 'Email hoặc mật khẩu không chính xác.';
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập — AutoHub Dealership</title>
<link rel="stylesheet" href="public/css/style.css">
</head>
<body class="login-body">
<div class="login-layout">
  <div class="login-hero">
    <div class="brand" style="border:none;padding:0">
      <div class="brand-icon">A</div>
      <div>
        <b>AutoHub</b>
        <small style="color:#94a3b8">HỆ THỐNG QUẢN LÝ HÃNG XE</small>
      </div>
    </div>
    <div style="margin:auto 0">
      <div style="font-size:11px;letter-spacing:1px;color:#38bdf8;font-weight:700;text-transform:uppercase;margin-bottom:12px">NỀN TẢNG QUẢN LÝ ĐẠI LÝ HÃNG XE</div>
      <h1>Vận hành thông minh.<br>Tăng trưởng bền vững.</h1>
      <p>Kết nối bán hàng, CRM, kho xe, phụ tùng, dịch vụ sửa chữa và tài chính kế toán trên một nền tảng quản trị duy nhất.</p>
      <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap">
        <span class="badge blue">✓ 10 Phân hệ nghiệp vụ</span>
        <span class="badge green">✓ Phân quyền RBAC 7 Roles</span>
        <span class="badge amber">✓ Bảo mật Session & CSRF</span>
      </div>
    </div>
    <div style="font-size:11px;color:#64748b">© <?=date('Y')?> AutoHub Enterprise Automotive Platform</div>
  </div>

  <div class="login-form-side">
    <div class="login-card-box card">
      <h2 style="font-size:22px;font-weight:700;margin-bottom:6px">Đăng nhập hệ thống</h2>
      <p class="muted" style="margin-bottom:20px">Vui lòng nhập tài khoản để tiếp tục công việc.</p>

      <?php if ($error): ?>
        <div class="toast danger" style="position:static;margin-bottom:16px;animation:none"><?=e($error)?></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
        <div style="display:flex;flex-direction:column;gap:14px">
          <label style="font-size:12px;font-weight:600">Email đăng nhập
            <input type="email" name="email" value="admin@autohub.vn" required autocomplete="username">
          </label>

          <label style="font-size:12px;font-weight:600">Mật khẩu
            <input type="password" name="password" value="password" required autocomplete="current-password">
          </label>

          <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:6px;font-size:11px;color:#64748b">
            Tài khoản demo: <b>admin@autohub.vn</b> / Mật khẩu: <b>password</b>
          </div>

          <button class="btn primary wide" style="padding:12px;margin-top:6px;font-size:13px">Đăng nhập hệ thống</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
