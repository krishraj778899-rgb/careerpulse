<?php
// CareerPulse - In-app Notifications API
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireAuth();
$pdo = getDB();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];
$input = array_merge($_POST, $jsonBody);

switch ($action) {
    case 'list':
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
        $stmt->execute([$user['id']]);
        $notifications = $stmt->fetchAll();

        $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $unreadStmt->execute([$user['id']]);
        $unreadCount = $unreadStmt->fetchColumn();

        jsonResponse([
            'success' => true,
            'unread_count' => intval($unreadCount),
            'notifications' => $notifications
        ]);
        break;

    case 'mark_read':
        $id = intval($input['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        jsonResponse(['success' => true]);
        break;

    case 'mark_all_read':
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
        break;

    default:
        jsonError('Unknown notification action.');
}
