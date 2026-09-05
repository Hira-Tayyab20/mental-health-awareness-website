<?php
// ============================================================
// contact.php — Contact / Feedback Form Handler
// ============================================================
require_once 'config.php';

$action = $_GET['action'] ?? 'send';

switch ($action) {

    // ----------------------------------------------------------
    // SEND MESSAGE
    // ----------------------------------------------------------
    case 'send':
        $body    = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $name    = clean($body['name']    ?? '');
        $email   = clean($body['email']   ?? '');
        $subject = clean($body['subject'] ?? '');
        $message = clean($body['message'] ?? '');

        // Validation
        if (!$name || !$email || !$subject || !$message)
            jsonResponse(['error' => 'All fields are required.'], 400);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['error' => 'Please enter a valid email address.'], 400);

        if (strlen($message) < 10)
            jsonResponse(['error' => 'Message is too short.'], 400);

        if (strlen($message) > 2000)
            jsonResponse(['error' => 'Message is too long (max 2000 characters).'], 400);

        // Save to database
        $db   = getDB();
        $stmt = $db->prepare(
            'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $subject, $message]);

        // Optional: send email notification (uncomment if mail() is configured on your server)
        /*
        $to      = ADMIN_EMAIL;
        $headers = "From: MindfulSpace <noreply@mindfulspace.pk>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $emailBody = "New contact message from MindfulSpace:\n\n"
                   . "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
        mail($to, "MindfulSpace Contact: $subject", $emailBody, $headers);
        */

        jsonResponse(['success' => true, 'message' => 'Your message has been sent. Thank you!']);
        break;

    // ----------------------------------------------------------
    // ADMIN: List Messages
    // ----------------------------------------------------------
    case 'list':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $db   = getDB();
        $msgs = $db->query(
            'SELECT id, name, email, subject, message, is_read,
                    DATE_FORMAT(created_at, "%b %d %Y, %h:%i %p") AS received_at
             FROM contact_messages
             ORDER BY created_at DESC
             LIMIT 100'
        )->fetchAll();

        jsonResponse(['messages' => $msgs]);
        break;

    // ----------------------------------------------------------
    // ADMIN: Mark as read
    // ----------------------------------------------------------
    case 'read':
        requireLogin();
        $user = getCurrentUser();
        if ($user['role'] !== 'admin') jsonResponse(['error' => 'Admins only.'], 403);

        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id   = (int)($body['id'] ?? 0);
        if (!$id) jsonResponse(['error' => 'id required.'], 400);

        $db = getDB();
        $db->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?')->execute([$id]);
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
