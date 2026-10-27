<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

if (!logged_in()) {
    json_response(['error' => 'Unauthorized'], 401);
}

verify_csrf();

$action = $_POST['action'] ?? '';
$pdo = db();

if ($action === 'mark_notification_read') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?');
    $stmt->execute([$id, user()['id']]);
    json_response(['success' => true]);
}

if ($action === 'update_opp_stage') {
    $id = (int)($_POST['id'] ?? 0);
    $stage = trim($_POST['stage'] ?? '');
    if ($id > 0 && !empty($stage)) {
        $stmt = $pdo->prepare('UPDATE sales_opportunities SET stage=? WHERE id=?');
        $stmt->execute([$stage, $id]);
        audit('UPDATE_STAGE', 'SalesOpportunity', $id, null, ['stage' => $stage]);
        json_response(['success' => true]);
    }
    json_response(['error' => 'Invalid data'], 400);
}

json_response(['error' => 'Action not supported'], 400);
