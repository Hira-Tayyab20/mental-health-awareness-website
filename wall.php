<?php
// ============================================================
// wall.php — Public Anonymous Mood Wall
// ============================================================
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    // ----------------------------------------------------------
    // POST TO MOOD WALL (anonymous)
    // ----------------------------------------------------------
    case 'post':
        $body    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $mood    = clean($body['mood']    ?? '');
        $emoji   = clean($body['emoji']  ?? '');
        $message = clean($body['message'] ?? '');

        $allowed = ['great','good','okay','stressed','sad','anxious'];
        if (!in_array($mood, $allowed, true))
            jsonResponse(['error' => 'Invalid mood.'], 400);

        if (strlen($message) > 280)
            jsonResponse(['error' => 'Message too long (max 280 characters).'], 400);

        // Simple rate-limit: max 5 posts per IP per hour (stored in DB)
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $db   = getDB();

        // We track by IP in a loose way via the message table itself
        $stmt = $db->prepare(
            'SELECT COUNT(*) as cnt FROM mood_wall
             WHERE ip_hash = ? AND created_at >= NOW() - INTERVAL 1 HOUR'
        );
        // Hash the IP for privacy
        $ipHash = hash('sha256', $ip . 'mindfulspace_salt');
        $stmt->execute([$ipHash]);
        $row = $stmt->fetch();
        if ((int)$row['cnt'] >= 5)
            jsonResponse(['error' => 'You\'ve posted too many times. Please wait an hour.'], 429);

        $stmt = $db->prepare(
            'INSERT INTO mood_wall (emoji, mood, message, ip_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$emoji, $mood, $message ?: null, $ipHash]);

        jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
        break;

    // ----------------------------------------------------------
    // GET RECENT WALL POSTS
    // ----------------------------------------------------------
    case 'feed':
        $db    = getDB();
        $limit = min((int)($_GET['limit'] ?? 20), 50);

        $stmt = $db->prepare(
            'SELECT id, emoji, mood, message,
                    DATE_FORMAT(created_at, "%b %d, %h:%i %p") AS posted_at
             FROM mood_wall
             ORDER BY created_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        jsonResponse(['posts' => $stmt->fetchAll()]);
        break;

    // ----------------------------------------------------------
    // GET MOOD COUNTS (for community stats)
    // ----------------------------------------------------------
    case 'stats':
        $db = getDB();
        $rows = $db->query(
            'SELECT mood, emoji, COUNT(*) AS count
             FROM mood_wall
             WHERE created_at >= NOW() - INTERVAL 24 HOUR
             GROUP BY mood, emoji
             ORDER BY count DESC'
        )->fetchAll();
        jsonResponse(['stats' => $rows]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
