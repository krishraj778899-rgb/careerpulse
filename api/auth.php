<?php
// CareerPulse - Authentication API Endpoint
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Read JSON input if sent as application/json
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];
$input = array_merge($_POST, $jsonBody);

switch ($action) {
    case 'status':
        $user = getCurrentUser();
        jsonResponse([
            'authenticated' => $user !== null,
            'user' => $user
        ]);
        break;

    case 'login':
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonError('Email and password are required.');
        }

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            jsonError('Invalid email or password.', 401);
        }

        if ($user['status'] === 'suspended') {
            jsonError('Your account has been suspended by an administrator. Please contact support.', 403);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        logActivity('USER_LOGIN', "User logged in with role: {$user['role']}", $user['id']);

        $fullUser = getCurrentUser();
        jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'user' => $fullUser
        ]);
        break;

    case 'register':
        $role = trim($input['role'] ?? 'student');
        if (!in_array($role, ['student', 'company'])) {
            jsonError('Invalid role specified for registration.');
        }

        $name = trim($input['name'] ?? '');
        $email = trim(filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password = $input['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            jsonError('Name, email, and password are required.');
        }

        if (strlen($password) < 6) {
            jsonError('Password must be at least 6 characters long.');
        }

        $pdo = getDB();
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonError('An account with this email already exists.');
        }

        $pdo->beginTransaction();
        try {
            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$name, $email, $passHash, $role]);
            $userId = $pdo->lastInsertId();

            if ($role === 'student') {
                $dept = trim($input['department'] ?? 'Computer Science');
                $cgpa = floatval($input['cgpa'] ?? 7.5);
                $year = intval($input['graduation_year'] ?? date('Y') + 1);
                $skills = trim($input['skills'] ?? '');
                $phone = trim($input['phone'] ?? '');

                $sStmt = $pdo->prepare("INSERT INTO student_profiles (user_id, phone, department, cgpa, graduation_year, skills) VALUES (?, ?, ?, ?, ?, ?)");
                $sStmt->execute([$userId, $phone, $dept, $cgpa, $year, $skills]);

                createNotification($userId, 'Welcome to CareerPulse', 'Your student profile is active. Browse job drives, check eligibility, and track your applications.', 'success', '#portal');
            } elseif ($role === 'company') {
                $compName = trim($input['company_name'] ?? $name);
                $website = trim($input['website'] ?? '');
                $industry = trim($input['industry'] ?? 'Technology');
                $location = trim($input['location'] ?? 'Bengaluru, India');
                $desc = trim($input['description'] ?? '');

                $cStmt = $pdo->prepare("INSERT INTO company_profiles (user_id, company_name, website, industry, location, description, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $cStmt->execute([$userId, $compName, $website, $industry, $location, $desc]);

                createNotification($userId, 'Welcome Recruiter', 'Your company account is set up. Start publishing hiring drives and discovering top student talent.', 'success', '#post-job');
            }

            $pdo->commit();

            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $role;

            logActivity('USER_REGISTER', "New $role account created: $name ($email)", $userId);

            $fullUser = getCurrentUser();
            jsonResponse([
                'success' => true,
                'message' => 'Registration successful!',
                'user' => $fullUser
            ], 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Registration failed: ' . $e->getMessage(), 500);
        }
        break;

    case 'update_profile':
        $user = requireAuth();
        $pdo = getDB();

        $name = trim($input['name'] ?? $user['name']);
        if (!empty($name)) {
            $uStmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $uStmt->execute([$name, $user['id']]);
        }

        if ($user['role'] === 'student') {
            $phone = trim($input['phone'] ?? ($user['phone'] ?? ''));
            $dept = trim($input['department'] ?? ($user['department'] ?? ''));
            $cgpa = isset($input['cgpa']) ? floatval($input['cgpa']) : ($user['cgpa'] ?? 8.0);
            $year = isset($input['graduation_year']) ? intval($input['graduation_year']) : ($user['graduation_year'] ?? 2026);
            $skills = trim($input['skills'] ?? ($user['skills'] ?? ''));
            $bio = trim($input['bio'] ?? ($user['bio'] ?? ''));
            $github = trim($input['github_url'] ?? ($user['github_url'] ?? ''));
            $linkedin = trim($input['linkedin_url'] ?? ($user['linkedin_url'] ?? ''));

            $sStmt = $pdo->prepare("UPDATE student_profiles SET phone = ?, department = ?, cgpa = ?, graduation_year = ?, skills = ?, bio = ?, github_url = ?, linkedin_url = ? WHERE user_id = ?");
            $sStmt->execute([$phone, $dept, $cgpa, $year, $skills, $bio, $github, $linkedin, $user['id']]);
        } elseif ($user['role'] === 'company') {
            $compName = trim($input['company_name'] ?? ($user['company_name'] ?? ''));
            $website = trim($input['website'] ?? ($user['website'] ?? ''));
            $industry = trim($input['industry'] ?? ($user['industry'] ?? ''));
            $location = trim($input['location'] ?? ($user['location'] ?? ''));
            $desc = trim($input['description'] ?? ($user['description'] ?? ''));

            $cStmt = $pdo->prepare("UPDATE company_profiles SET company_name = ?, website = ?, industry = ?, location = ?, description = ? WHERE user_id = ?");
            $cStmt->execute([$compName, $website, $industry, $location, $desc, $user['id']]);
        }

        logActivity('PROFILE_UPDATED', "User updated their profile", $user['id']);

        jsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => getCurrentUser()
        ]);
        break;

    case 'upload_resume':
        $user = requireRole('student');
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            jsonError('No file uploaded or upload error occurred.');
        }

        $file = $_FILES['resume'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'txt'];

        if (!in_array($ext, $allowed)) {
            jsonError('Invalid file type. Only PDF, DOC, DOCX, and TXT files are allowed.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            jsonError('File size exceeds the 5MB limit.');
        }

        $uploadDir = __DIR__ . '/../assets/uploads/resumes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'resume_' . $user['id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE student_profiles SET resume_filename = ? WHERE user_id = ?");
            $stmt->execute([$filename, $user['id']]);

            logActivity('RESUME_UPLOADED', "Uploaded resume: $filename", $user['id']);

            jsonResponse([
                'success' => true,
                'message' => 'Resume uploaded successfully!',
                'filename' => $filename
            ]);
        } else {
            jsonError('Failed to save uploaded file.', 500);
        }
        break;

    case 'upload_avatar':
        $user = requireAuth();
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            jsonError('No image uploaded or an upload error occurred.');
        }

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($ext, $allowed)) {
            jsonError('Invalid image format. Allowed formats: JPG, PNG, WEBP, SVG.');
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            jsonError('Image file size exceeds the 3MB limit.');
        }

        $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE users SET avatar_filename = ? WHERE id = ?");
            $stmt->execute([$filename, $user['id']]);

            if ($user['role'] === 'company') {
                $cStmt = $pdo->prepare("UPDATE company_profiles SET logo_url = ? WHERE user_id = ?");
                $cStmt->execute(['assets/uploads/avatars/' . $filename, $user['id']]);
            }

            logActivity('AVATAR_UPLOADED', "Uploaded new profile picture: $filename", $user['id']);

            $updatedUser = getCurrentUser();
            jsonResponse([
                'success' => true,
                'message' => 'Profile picture updated successfully.',
                'avatar_url' => 'assets/uploads/avatars/' . $filename,
                'user' => $updatedUser
            ]);
        } else {
            jsonError('Failed to save avatar image file.', 500);
        }
        break;

    case 'logout':
        if (isset($_SESSION['user_id'])) {
            logActivity('USER_LOGOUT', "User logged out", $_SESSION['user_id']);
        }
        session_unset();
        session_destroy();
        jsonResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
        break;

    default:
        jsonError('Unknown auth action: ' . htmlspecialchars($action));
}
