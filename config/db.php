<?php
// CareerPulse - Database Configuration & Common Backend Helpers
// Engineered for WAMP Server (PHP 8.x + MySQL 8.x)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'careerpulse_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If database doesn't exist, try to connect to server and initialize
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $initPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS);
                require_once __DIR__ . '/setup.php';
                runDatabaseSetup($initPdo, false);
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (Exception $initEx) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Database connection and initialization failed: ' . $initEx->getMessage()]);
                exit;
            }
        }

        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

// Ensure database tables exist on first run
function ensureDatabaseReady() {
    $pdo = getDB();
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if (!$check) {
            require_once __DIR__ . '/setup.php';
            runDatabaseSetup($pdo, false);
        } else {
            // Check for avatar_filename column
            $colCheck = $pdo->query("SHOW COLUMNS FROM users LIKE 'avatar_filename'")->fetch();
            if (!$colCheck) {
                $pdo->exec("ALTER TABLE users ADD COLUMN avatar_filename VARCHAR(255) NULL AFTER status");
            }
        }

        // Ensure upload directories exist
        $dirs = [
            __DIR__ . '/../assets/uploads/resumes',
            __DIR__ . '/../assets/uploads/avatars'
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    } catch (Exception $e) {
        // ignore
    }
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// JSON error response helper
function jsonError($message, $statusCode = 400, $extra = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    $payload = array_merge(['success' => false, 'error' => $message], $extra);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Get current logged-in user from session
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, name, email, role, status, avatar_filename FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] !== 'active') {
        session_unset();
        session_destroy();
        return null;
    }

    $user['avatar_url'] = !empty($user['avatar_filename']) 
        ? 'assets/uploads/avatars/' . $user['avatar_filename'] 
        : null;

    // Attach role-specific profile ID and details
    if ($user['role'] === 'student') {
        $sStmt = $pdo->prepare("SELECT id as student_id, phone, department, cgpa, graduation_year, skills, resume_filename, bio, github_url, linkedin_url FROM student_profiles WHERE user_id = ?");
        $sStmt->execute([$user['id']]);
        $profile = $sStmt->fetch() ?: [];
        $user = array_merge($user, $profile);
    } elseif ($user['role'] === 'company') {
        $cStmt = $pdo->prepare("SELECT id as company_id, company_name, website, industry, location, logo_url, description, is_verified FROM company_profiles WHERE user_id = ?");
        $cStmt->execute([$user['id']]);
        $profile = $cStmt->fetch() ?: [];
        $user = array_merge($user, $profile);
    }

    return $user;
}

// Enforce authentication
function requireAuth() {
    $user = getCurrentUser();
    if (!$user) {
        jsonError('Authentication required. Please log in.', 401);
    }
    return $user;
}

// Enforce role authorization
function requireRole($allowedRoles) {
    $user = requireAuth();
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    if (!in_array($user['role'], $allowedRoles)) {
        jsonError('Unauthorized access for role: ' . $user['role'], 403);
    }
    return $user;
}

// Activity audit logger
function logActivity($action, $details = null, $userId = null) {
    try {
        $pdo = getDB();
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (Exception $e) {
        // silent fail on logging to not block critical workflows
    }
}

// In-app notification dispatcher
function createNotification($userId, $title, $message, $type = 'info', $link = null) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type, $link]);
    } catch (Exception $e) {
        // silent fail
    }
}
