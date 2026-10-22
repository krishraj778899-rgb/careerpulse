<?php
// CareerPulse - Admin Analytics, Moderation & Control Panel API
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'stats';
$pdo = getDB();

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];
$input = array_merge($_POST, $jsonBody);

// Allow public access ONLY to 'stats' or 'public_stats' for the placement-stats page!
if (!in_array($action, ['stats', 'public_stats'])) {
    $user = requireRole('admin');
}

switch ($action) {
    case 'stats':
    case 'public_stats':
        // Core metrics
        $totalStudents = $pdo->query("SELECT COUNT(*) FROM student_profiles")->fetchColumn();
        $totalCompanies = $pdo->query("SELECT COUNT(*) FROM company_profiles")->fetchColumn();
        $totalJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'approved'")->fetchColumn();
        $pendingJobs = $pdo->query("SELECT COUNT(*) FROM jobs WHERE status = 'pending'")->fetchColumn();
        $totalApplications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
        $totalOffers = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Offered'")->fetchColumn();
        $totalInterviews = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'Interviewing'")->fetchColumn();

        // Overall placement rate
        $placedStudents = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM applications WHERE status = 'Offered'")->fetchColumn();
        $overallPlacementRate = $totalStudents > 0 ? round(($placedStudents / $totalStudents) * 100, 1) : 0;

        // Branch-wise placement distribution
        $branchSql = "SELECT sp.department,
                             COUNT(DISTINCT sp.id) AS total_students,
                             COUNT(DISTINCT CASE WHEN a.status = 'Offered' THEN sp.id END) AS placed_students
                      FROM student_profiles sp
                      LEFT JOIN applications a ON sp.id = a.student_id
                      GROUP BY sp.department";
        $branchStats = $pdo->query($branchSql)->fetchAll();
        foreach ($branchStats as &$bs) {
            $bs['placement_rate'] = $bs['total_students'] > 0 
                ? round(($bs['placed_students'] / $bs['total_students']) * 100, 1) 
                : 0;
        }

        // Top recruiters
        $recruiterSql = "SELECT cp.company_name, cp.logo_url, cp.industry,
                                COUNT(DISTINCT j.id) AS total_jobs,
                                COUNT(DISTINCT a.id) AS total_applications,
                                COUNT(DISTINCT CASE WHEN a.status = 'Offered' THEN a.id END) AS offers_made
                         FROM company_profiles cp
                         LEFT JOIN jobs j ON cp.id = j.company_id
                         LEFT JOIN applications a ON j.id = a.job_id
                         GROUP BY cp.id
                         ORDER BY total_applications DESC
                         LIMIT 6";
        $topRecruiters = $pdo->query($recruiterSql)->fetchAll();

        // Applications per drive
        $driveSql = "SELECT j.id, j.title, cp.company_name, j.type, j.deadline, j.status,
                            COUNT(a.id) AS application_count
                     FROM jobs j
                     JOIN company_profiles cp ON j.company_id = cp.id
                     LEFT JOIN applications a ON j.id = a.job_id
                     GROUP BY j.id
                     ORDER BY application_count DESC
                     LIMIT 8";
        $driveStats = $pdo->query($driveSql)->fetchAll();

        // Recent platform activity
        $recentLogs = $pdo->query("SELECT al.*, u.name as user_name, u.role as user_role 
                                   FROM activity_logs al 
                                   LEFT JOIN users u ON al.user_id = u.id 
                                   ORDER BY al.created_at DESC 
                                   LIMIT 10")->fetchAll();

        jsonResponse([
            'success' => true,
            'metrics' => [
                'total_students' => intval($totalStudents),
                'total_companies' => intval($totalCompanies),
                'total_jobs' => intval($totalJobs),
                'pending_jobs' => intval($pendingJobs),
                'total_applications' => intval($totalApplications),
                'total_offers' => intval($totalOffers),
                'total_interviews' => intval($totalInterviews),
                'overall_placement_rate' => $overallPlacementRate,
                'placed_students' => intval($placedStudents)
            ],
            'branch_distribution' => $branchStats,
            'top_recruiters' => $topRecruiters,
            'drives_breakdown' => $driveStats,
            'recent_logs' => $recentLogs
        ]);
        break;

    case 'pending_jobs':
        $sql = "SELECT j.*, cp.company_name, cp.website, cp.logo_url, u.email as company_email
                FROM jobs j
                JOIN company_profiles cp ON j.company_id = cp.id
                JOIN users u ON cp.user_id = u.id
                WHERE j.status = 'pending'
                ORDER BY j.created_at DESC";
        $jobs = $pdo->query($sql)->fetchAll();
        jsonResponse(['success' => true, 'pending_jobs' => $jobs]);
        break;

    case 'approve_job':
        $jobId = intval($input['job_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT j.*, cp.user_id as company_user_id, cp.company_name FROM jobs j JOIN company_profiles cp ON j.company_id = cp.id WHERE j.id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            jsonError('Job posting not found.', 404);
        }

        $stmt = $pdo->prepare("UPDATE jobs SET status = 'approved' WHERE id = ?");
        $stmt->execute([$jobId]);

        logActivity('ADMIN_APPROVE_JOB', "Admin approved job #{$jobId} ({$job['title']})", $_SESSION['user_id']);
        createNotification($job['company_user_id'], 'Job Posting Approved', "Your posting '{$job['title']}' has been reviewed and approved by Admin. It is now live.", 'success', '#manage-drives');

        jsonResponse(['success' => true, 'message' => "Job '{$job['title']}' approved successfully."]);
        break;

    case 'reject_job':
        $jobId = intval($input['job_id'] ?? 0);
        $reason = trim($input['reason'] ?? 'Did not meet listing criteria');

        $stmt = $pdo->prepare("SELECT j.*, cp.user_id as company_user_id, cp.company_name FROM jobs j JOIN company_profiles cp ON j.company_id = cp.id WHERE j.id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            jsonError('Job posting not found.', 404);
        }

        $stmt = $pdo->prepare("UPDATE jobs SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$jobId]);

        logActivity('ADMIN_REJECT_JOB', "Admin rejected job #{$jobId} ({$job['title']}). Reason: $reason", $_SESSION['user_id']);
        createNotification($job['company_user_id'], 'Job Posting Rejected', "Your posting '{$job['title']}' was rejected: {$reason}", 'danger', '#manage-drives');

        jsonResponse(['success' => true, 'message' => "Job '{$job['title']}' has been rejected."]);
        break;

    case 'users_list':
        $roleFilter = trim($_GET['role'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $sql = "SELECT u.id, u.name, u.email, u.role, u.status, u.created_at,
                       sp.department, sp.cgpa, sp.graduation_year,
                       cp.company_name, cp.industry, cp.is_verified
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                LEFT JOIN company_profiles cp ON u.id = cp.user_id
                WHERE u.role != 'admin'";

        $params = [];
        if (!empty($roleFilter) && $roleFilter !== 'All') {
            $sql .= " AND u.role = ?";
            $params[] = $roleFilter;
        }
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR cp.company_name LIKE ? OR sp.department LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        jsonResponse(['success' => true, 'users' => $users]);
        break;

    case 'toggle_user_status':
        $targetUserId = intval($input['user_id'] ?? 0);
        $newStatus = trim($input['status'] ?? '');

        if (!in_array($newStatus, ['active', 'suspended'])) {
            jsonError('Invalid status.');
        }

        if ($targetUserId === $_SESSION['user_id']) {
            jsonError('You cannot alter your own admin status.');
        }

        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->execute([$newStatus, $targetUserId]);

        logActivity('USER_STATUS_TOGGLED', "Admin set user #{$targetUserId} status to {$newStatus}", $_SESSION['user_id']);

        jsonResponse(['success' => true, 'message' => "User account status updated to {$newStatus}."]);
        break;

    case 'verify_company':
        $companyId = intval($input['company_id'] ?? 0);
        $verified = intval($input['is_verified'] ?? 1);

        $stmt = $pdo->prepare("UPDATE company_profiles SET is_verified = ? WHERE id = ?");
        $stmt->execute([$verified, $companyId]);

        logActivity('COMPANY_VERIFICATION', "Admin toggled verification for company #{$companyId} to {$verified}", $_SESSION['user_id']);

        jsonResponse(['success' => true, 'message' => "Company verification badge updated."]);
        break;

    case 'activity_logs':
        $stmt = $pdo->query("SELECT al.*, u.name as user_name, u.role as user_role 
                             FROM activity_logs al 
                             LEFT JOIN users u ON al.user_id = u.id 
                             ORDER BY al.created_at DESC 
                             LIMIT 100");
        $logs = $stmt->fetchAll();
        jsonResponse(['success' => true, 'logs' => $logs]);
        break;

    default:
        jsonError('Unknown admin action: ' . htmlspecialchars($action));
}
