<?php
// ============================================================
// resources.php — Dynamic Resources + Favorites API
// ============================================================
require_once 'config.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {

    // ----------------------------------------------------------
    // GET ALL ACTIVE RESOURCES (public)
    // ----------------------------------------------------------
    case 'list':
        $db   = getDB();
        $user = getCurrentUser();

        $resources = $db->query(
            'SELECT id, icon, title, description, link, link_text
             FROM resources WHERE is_active = 1 ORDER BY sort_order ASC'
        )->fetchAll();

        // If logged in, attach which ones are favorited
        $favIds = [];
        if ($user) {
            $stmt = $db->prepare('SELECT resource_id FROM favorites WHERE user_id = ?');
            $stmt->execute([$user['id']]);
            $favIds = array_column($stmt->fetchAll(), 'resource_id');
        }

        foreach ($resources as &$r) {
            $r['is_favorite'] = in_array((int)$r['id'], array_map('intval', $favIds));
        }

        jsonResponse(['resources' => $resources]);
        break;

    // ----------------------------------------------------------
    // TOGGLE FAVORITE
    // ----------------------------------------------------------
    case 'favorite':
        requireLogin();
        $user = getCurrentUser();
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rid  = (int)($body['resource_id'] ?? 0);

        if (!$rid) jsonResponse(['error' => 'resource_id required.'], 400);

        $db   = getDB();
        $stmt = $db->prepare('SELECT id FROM favorites WHERE user_id = ? AND resource_id = ?');
        $stmt->execute([$user['id'], $rid]);

        if ($stmt->fetch()) {
            // Already favorited → remove
            $db->prepare('DELETE FROM favorites WHERE user_id = ? AND resource_id = ?')
               ->execute([$user['id'], $rid]);
            jsonResponse(['favorited' => false]);
        } else {
            // Not favorited → add
            $db->prepare('INSERT INTO favorites (user_id, resource_id) VALUES (?, ?)')
               ->execute([$user['id'], $rid]);
            jsonResponse(['favorited' => true]);
        }
        break;

    // ----------------------------------------------------------
    // GET USER FAVORITES
    // ----------------------------------------------------------
    case 'favorites':
        requireLogin();
        $user = getCurrentUser();
        $db   = getDB();

        $stmt = $db->prepare(
            'SELECT r.id, r.icon, r.title, r.description, r.link, r.link_text
             FROM resources r
             JOIN favorites f ON f.resource_id = r.id
             WHERE f.user_id = ? AND r.is_active = 1
             ORDER BY f.created_at DESC'
        );
        $stmt->execute([$user['id']]);
        jsonResponse(['favorites' => $stmt->fetchAll()]);
        break;

    // ----------------------------------------------------------
    // ADMIN: ADD RESOURCE
    // ----------------------------------------------------------
    case 'add':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $icon      = clean($body['icon']        ?? '💚');
        $title     = clean($body['title']       ?? '');
        $desc      = clean($body['description'] ?? '');
        $link      = clean($body['link']        ?? '');
        $linkText  = clean($body['link_text']   ?? 'Learn More');

        if (!$title || !$desc || !$link)
            jsonResponse(['error' => 'title, description, and link are required.'], 400);

        if (!filter_var($link, FILTER_VALIDATE_URL))
            jsonResponse(['error' => 'Invalid URL.'], 400);

        $db = getDB();
        $db->prepare(
            'INSERT INTO resources (icon, title, description, link, link_text) VALUES (?, ?, ?, ?, ?)'
        )->execute([$icon, $title, $desc, $link, $linkText]);

        jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
        break;

    // ----------------------------------------------------------
    // ADMIN: EDIT RESOURCE
    // ----------------------------------------------------------
    case 'edit':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $body      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id        = (int)($body['id']          ?? 0);
        $icon      = clean($body['icon']        ?? '💚');
        $title     = clean($body['title']       ?? '');
        $desc      = clean($body['description'] ?? '');
        $link      = clean($body['link']        ?? '');
        $linkText  = clean($body['link_text']   ?? 'Learn More');
        $isActive  = (int)($body['is_active']   ?? 1);

        if (!$id || !$title || !$desc || !$link)
            jsonResponse(['error' => 'id, title, description, and link are required.'], 400);

        $db = getDB();
        $db->prepare(
            'UPDATE resources SET icon=?, title=?, description=?, link=?, link_text=?, is_active=?, updated_at=NOW()
             WHERE id=?'
        )->execute([$icon, $title, $desc, $link, $linkText, $isActive, $id]);

        jsonResponse(['success' => true]);
        break;

    // ----------------------------------------------------------
    // ADMIN: DELETE RESOURCE
    // ----------------------------------------------------------
    case 'delete':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id   = (int)($body['id'] ?? 0);

        if (!$id) jsonResponse(['error' => 'id required.'], 400);

        $db = getDB();
        $db->prepare('DELETE FROM resources WHERE id = ?')->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    // ----------------------------------------------------------
    // ADMIN: LIST ALL (including inactive)
    // ----------------------------------------------------------
    case 'admin_list':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $db = getDB();
        $resources = $db->query(
            'SELECT id, icon, title, description, link, link_text, is_active, sort_order, created_at
             FROM resources ORDER BY sort_order ASC'
        )->fetchAll();

        jsonResponse(['resources' => $resources]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
