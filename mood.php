<?php
// ============================================================
// mood.php — Save & Retrieve Mood History (server-side)
// ============================================================
require_once 'config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {

    // ----------------------------------------------------------
    // SAVE MOOD (POST)
    // ----------------------------------------------------------
    case 'save':
        requireLogin();
        $user = getCurrentUser();

        $body  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $mood  = clean($body['mood']  ?? '');
        $label = clean($body['label'] ?? '');
        $emoji = clean($body['emoji'] ?? '');

        $allowed = ['great','good','okay','stressed','sad','anxious'];
        if (!in_array($mood, $allowed, true))
            jsonResponse(['error' => 'Invalid mood value.'], 400);

        $db   = getDB();
        $stmt = $db->prepare('INSERT INTO mood_history (user_id, mood, label, emoji) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user['id'], $mood, $label, $emoji]);

        jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
        break;

    // ----------------------------------------------------------
    // GET HISTORY (last 7 days)
    // ----------------------------------------------------------
    case 'history':
        requireLogin();
        $user = getCurrentUser();
        $db   = getDB();

        $stmt = $db->prepare(
            'SELECT id, mood, label, emoji,
                    DATE_FORMAT(created_at, "%b %d") AS date,
                    DATE_FORMAT(created_at, "%h:%i %p") AS time,
                    UNIX_TIMESTAMP(created_at) * 1000 AS ts
             FROM mood_history
             WHERE user_id = ?
               AND created_at >= NOW() - INTERVAL 7 DAY
             ORDER BY created_at DESC
             LIMIT 50'
        );
        $stmt->execute([$user['id']]);
        jsonResponse(['history' => $stmt->fetchAll()]);
        break;

    // ----------------------------------------------------------
    // MOOD STATS (for profile page)
    // ----------------------------------------------------------
    case 'stats':
        requireLogin();
        $user = getCurrentUser();
        $db   = getDB();

        $stmt = $db->prepare(
            'SELECT mood, label, emoji, COUNT(*) AS count
             FROM mood_history
             WHERE user_id = ?
             GROUP BY mood, label, emoji
             ORDER BY count DESC'
        );
        $stmt->execute([$user['id']]);
        jsonResponse(['stats' => $stmt->fetchAll()]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
