<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
if (logged_in()) redirect('../index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare("SELECT id,name,email,password_hash,role,branch_id FROM users WHERE email=? AND status='active' LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$row['id'], 'name'=>$row['name'], 'email'=>$row['email'],
            'role'=>$row['role'], 'branch_id'=>$row['branch_id'] === null ? null : (int)$row['branch_id']
        ];
        audit('LOGIN','User',$row['id'],null,['email'=>$row['email']]);
        redirect('../index.php');
    }
    $error = 'Email hoặc mật khẩu không đúng.';
}
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Đăng nhập — AutoHub</title><link rel="stylesheet" href="../public/css/style.css"></head>
<body class="login-body"><div class="login-layout"><div class="login-showcase"><div class="brand light"><span class="logo-square">A</span><div><b>AutoHub</b><small>QUẢN LÝ ĐẠI LÝ</small></div></div><div class="login-hero-copy"><div class="eyebrow">NỀN TẢNG QUẢN LÝ ĐẠI LÝ TOÀN DIỆN</div><h1>Vận hành thông minh.<br>Tăng trưởng bền vững.</h1><p>Kết nối bán hàng, CRM, kho xe, phụ tùng, dịch vụ và tài chính trên một nền tảng duy nhất.</p><div class="hero-pills"><span>✓ Dữ liệu thời gian thực</span><span>✓ Bảo mật doanh nghiệp</span><span>✓ Hỗ trợ 24/7</span></div></div></div>
<div class="login-panel"><form method="post" class="login-card"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><h2>Chào mừng trở lại</h2><p class="muted">Đăng nhập để tiếp tục quản lý hoạt động đại lý.</p><?php if ($error): ?><div class="alert red-bg"><?=e($error)?></div><?php endif; ?><label>Email<input type="email" name="email" value="admin@autohub.vn" required autocomplete="username"></label><label>Mật khẩu<div class="password-wrap"><input id="password" type="password" name="password" value="password" required autocomplete="current-password"><button type="button" onclick="togglePassword()">◉</button></div></label><div class="login-row"><label class="check"><input type="checkbox" checked> Ghi nhớ đăng nhập</label><a href="#">Quên mật khẩu?</a></div><button class="btn primary wide">Đăng nhập</button><div class="login-safe">⌾ Phiên đăng nhập được bảo vệ bằng session + CSRF</div></form><div class="login-copy">© <?=date('Y')?> AutoHub • Demo chạy local</div></div></div><script src="../public/js/app.js"></script><script>function togglePassword(){const x=document.getElementById('password');x.type=x.type==='password'?'text':'password';}</script></body></html>
