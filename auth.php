<?php
// ============================================================
// auth.php — User Registration, Login, Logout, Session Check
// ============================================================
require_once 'config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // ----------------------------------------------------------
    // REGISTER
    // ----------------------------------------------------------
    case 'register':
        $name     = clean($_POST['name']     ?? '');
        $email    = clean($_POST['email']    ?? '');
        $password =       $_POST['password'] ?? '';
        $confirm  =       $_POST['confirm']  ?? '';

        // Validation
        if (!$name || !$email || !$password || !$confirm)
            jsonResponse(['error' => 'All fields are required.'], 400);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            jsonResponse(['error' => 'Invalid email address.'], 400);

        if (strlen($password) < 8)
            jsonResponse(['error' => 'Password must be at least 8 characters.'], 400);

        if ($password !== $confirm)
            jsonResponse(['error' => 'Passwords do not match.'], 400);

        $db = getDB();

        // Check duplicate email
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch())
            jsonResponse(['error' => 'This email is already registered.'], 409);

        // Insert user
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        $userId = $db->lastInsertId();

        // Auto-login after register
        $_SESSION['user'] = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'user'];
        jsonResponse(['success' => true, 'user' => $_SESSION['user']]);
        break;

    // ----------------------------------------------------------
    // LOGIN
    // ----------------------------------------------------------
    case 'login':
        $email    = clean($_POST['email']    ?? '');
        $password =       $_POST['password'] ?? '';

        if (!$email || !$password)
            jsonResponse(['error' => 'Email and password are required.'], 400);

        $db   = getDB();
        $stmt = $db->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password']))
            jsonResponse(['error' => 'Invalid email or password.'], 401);

        unset($user['password']); // never store password in session
        $_SESSION['user'] = $user;
        session_regenerate_id(true);

        jsonResponse(['success' => true, 'user' => $user]);
        break;

    // ----------------------------------------------------------
    // LOGOUT
    // ----------------------------------------------------------
    case 'logout':
        $_SESSION = [];
        session_destroy();
        jsonResponse(['success' => true]);
        break;

    // ----------------------------------------------------------
    // GET CURRENT SESSION
    // ----------------------------------------------------------
    case 'me':
        $user = getCurrentUser();
        if ($user) jsonResponse(['loggedIn' => true,  'user' => $user]);
        else        jsonResponse(['loggedIn' => false, 'user' => null]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action.'], 400);
}
