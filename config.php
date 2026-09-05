<?php
// ============================================================
// config.php — Database & App Configuration
// ============================================================

// --- Database Settings (update these after MySQL setup) ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'mindfulspace');
define('DB_USER', 'root');        // change to your MySQL username
define('DB_PASS', '');            // change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

// --- App Settings ---
define('APP_NAME', 'MindfulSpace');
define('APP_URL',  'http://localhost/mindfulspace'); // change to your URL
define('ADMIN_EMAIL', 'admin@example.com');          // change to your email

// --- Session ---
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days

// --- Database Connection (PDO) ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed. Please check config.php']));
        }
    }
    return $pdo;
}

// --- Start Session ---
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

// --- Helper: JSON response ---
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- Helper: Get logged-in user ---
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

// --- Helper: Require login ---
function requireLogin(): void {
    if (!getCurrentUser()) {
        jsonResponse(['error' => 'Not authenticated'], 401);
    }
}

// --- Helper: Sanitize input ---
function clean(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// --- CORS headers for API calls from JS ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
