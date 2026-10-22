<?php
// CareerPulse - Student Personal Application Tracker & Kanban API
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$user = requireRole('student');
$studentId = $user['student_id'];
$pdo = getDB();

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];
$input = array_merge($_POST, $jsonBody);

switch ($action) {
    case 'list':
        // Retrieve all applications for this student with company and job details
        $sql = "SELECT a.*, 
                       CASE 
                           WHEN a.is_external = 1 THEN a.external_company 
                           ELSE cp.company_name 
                       END AS company_name,
                       CASE 
                           WHEN a.is_external = 1 THEN a.external_title 
                           ELSE j.title 
                       END AS role_title,
                       j.salary_range,
                       j.deadline,
                       j.type AS job_type,
                       j.workplace_type,
                       j.location,
                       cp.logo_url,
                       (SELECT COUNT(*) FROM interview_notes WHERE application_id = a.id) AS notes_count,
                       (SELECT COUNT(*) FROM application_history WHERE application_id = a.id) AS history_count
                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
                LEFT JOIN company_profiles cp ON j.company_id = cp.id
                WHERE a.student_id = ?
                ORDER BY a.updated_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $apps = $stmt->fetchAll();

        // Calculate smart reminders
        $reminders = [];
        $now = new DateTime();

        foreach ($apps as &$app) {
            $updatedAt = new DateTime($app['updated_at']);
            $diffDays = $now->diff($updatedAt)->days;

            // Follow-up reminder if applied or screening for > 7 days
            if (in_array($app['status'], ['Applied', 'Screening']) && $diffDays >= 7) {
                $reminders[] = [
                    'type' => 'follow_up',
                    'app_id' => $app['id'],
                    'company' => $app['company_name'],
                    'role' => $app['role_title'],
                    'message' => "It has been {$diffDays} days since your last status update with {$app['company_name']}. Consider sending a polite follow-up inquiry."
                ];
            }

            // Interview reminder if scheduled in future
            if (!empty($app['interview_date'])) {
                $iDate = new DateTime($app['interview_date']);
                $diffHours = ($iDate->getTimestamp() - $now->getTimestamp()) / 3600;
                if ($diffHours > 0 && $diffHours <= 48) {
                    $reminders[] = [
                        'type' => 'interview_soon',
                        'app_id' => $app['id'],
                        'company' => $app['company_name'],
                        'role' => $app['role_title'],
                        'time' => $app['interview_date'],
                        'message' => "Upcoming interview with {$app['company_name']} on " . $iDate->format('M d, Y \a\t h:i A') . "!"
                    ];
                }
            }
        }

        // Summary metrics
        $metrics = [
            'total' => count($apps),
            'wishlist' => 0,
            'applied' => 0,
            'screening' => 0,
            'interviewing' => 0,
            'offered' => 0,
            'rejected' => 0,
        ];
        foreach ($apps as $a) {
            $st = strtolower($a['status']);
            if (isset($metrics[$st])) {
                $metrics[$st]++;
            }
        }
        $interviewCount = $metrics['interviewing'] + $metrics['offered'];
        $metrics['interview_rate'] = $metrics['total'] > 0 ? round(($interviewCount / $metrics['total']) * 100, 1) : 0;
        $metrics['offer_rate'] = $metrics['total'] > 0 ? round(($metrics['offered'] / $metrics['total']) * 100, 1) : 0;

        jsonResponse([
            'success' => true,
            'applications' => $apps,
            'metrics' => $metrics,
            'reminders' => $reminders
        ]);
        break;

    case 'update_status':
        // Move card in Kanban board or change status dropdown
        $appId = intval($input['application_id'] ?? 0);
        $newStatus = trim($input['status'] ?? '');
        $allowedStatuses = ['Wishlist', 'Applied', 'Screening', 'Interviewing', 'Offered', 'Rejected'];

        if (!in_array($newStatus, $allowedStatuses)) {
            jsonError('Invalid application status provided.');
        }

        // Verify ownership
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ? AND student_id = ?");
        $stmt->execute([$appId, $studentId]);
        $app = $stmt->fetch();

        if (!$app) {
            jsonError('Application not found or unauthorized.', 404);
        }

        $oldStatus = $app['status'];
        if ($oldStatus !== $newStatus) {
            $notes = trim($input['notes'] ?? "Status updated from {$oldStatus} to {$newStatus}");
            
            $pdo->beginTransaction();
            try {
                $uStmt = $pdo->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
                $uStmt->execute([$newStatus, $appId]);

                $hStmt = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status, notes, changed_by_user_id) VALUES (?, ?, ?, ?, ?)");
                $hStmt->execute([$appId, $oldStatus, $newStatus, $notes, $user['id']]);

                $pdo->commit();

                logActivity('STATUS_UPDATE', "Application #{$appId} moved from {$oldStatus} to {$newStatus}", $user['id']);

                createNotification($user['id'], "Application Status Updated", "Your application was updated to: {$newStatus}", 'info', '#tracker');
            } catch (Exception $e) {
                $pdo->rollBack();
                jsonError('Failed to update status: ' . $e->getMessage(), 500);
            }
        }

        jsonResponse([
            'success' => true,
            'message' => "Application status updated to {$newStatus}",
            'new_status' => $newStatus
        ]);
        break;

    case 'add_external':
        // Manually track off-campus application
        $companyName = trim($input['company_name'] ?? '');
        $roleTitle = trim($input['role_title'] ?? '');
        $status = trim($input['status'] ?? 'Applied');
        $coverNote = trim($input['notes'] ?? '');
        $interviewDate = !empty($input['interview_date']) ? $input['interview_date'] : null;

        if (empty($companyName) || empty($roleTitle)) {
            jsonError('Company name and role title are required.');
        }

        $stmt = $pdo->prepare("INSERT INTO applications (student_id, is_external, external_company, external_title, status, cover_note, interview_date) VALUES (?, 1, ?, ?, ?, ?, ?)");
        $stmt->execute([$studentId, $companyName, $roleTitle, $status, $coverNote, $interviewDate]);
        $appId = $pdo->lastInsertId();

        // History
        $hStmt = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status, notes, changed_by_user_id) VALUES (?, NULL, ?, 'Manually created external tracker entry', ?)");
        $hStmt->execute([$appId, $status, $user['id']]);

        logActivity('EXTERNAL_APP_ADDED', "Added external tracker entry for {$companyName} - {$roleTitle}", $user['id']);

        jsonResponse([
            'success' => true,
            'message' => 'Application added to your personal tracker!',
            'application_id' => $appId
        ], 201);
        break;

    case 'get_details':
        $appId = intval($_GET['application_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT a.*, 
                                      CASE WHEN a.is_external = 1 THEN a.external_company ELSE cp.company_name END AS company_name,
                                      CASE WHEN a.is_external = 1 THEN a.external_title ELSE j.title END AS role_title,
                                      j.location, j.type, j.workplace_type, j.salary_range, j.description, j.requirements
                               FROM applications a
                               LEFT JOIN jobs j ON a.job_id = j.id
                               LEFT JOIN company_profiles cp ON j.company_id = cp.id
                               WHERE a.id = ? AND a.student_id = ?");
        $stmt->execute([$appId, $studentId]);
        $app = $stmt->fetch();

        if (!$app) {
            jsonError('Application not found', 404);
        }

        // Timeline history
        $hStmt = $pdo->prepare("SELECT * FROM application_history WHERE application_id = ? ORDER BY created_at ASC");
        $hStmt->execute([$appId]);
        $history = $hStmt->fetchAll();

        // Interview notes
        $nStmt = $pdo->prepare("SELECT * FROM interview_notes WHERE application_id = ? ORDER BY created_at DESC");
        $nStmt->execute([$appId]);
        $notes = $nStmt->fetchAll();

        jsonResponse([
            'success' => true,
            'application' => $app,
            'history' => $history,
            'notes' => $notes
        ]);
        break;

    case 'add_interview_note':
        $appId = intval($input['application_id'] ?? 0);
        $round = trim($input['round_name'] ?? 'Interview Round');
        $date = !empty($input['interview_date']) ? $input['interview_date'] : date('Y-m-d');
        $questions = trim($input['questions_asked'] ?? '');
        $notes = trim($input['notes'] ?? '');
        $result = trim($input['result'] ?? 'Pending');

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM applications WHERE id = ? AND student_id = ?");
        $stmt->execute([$appId, $studentId]);
        if (!$stmt->fetch()) {
            jsonError('Application not found or unauthorized', 403);
        }

        $stmt = $pdo->prepare("INSERT INTO interview_notes (application_id, round_name, interview_date, questions_asked, notes, result) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$appId, $round, $date, $questions, $notes, $result]);

        logActivity('INTERVIEW_NOTE_ADDED', "Added interview note for round: {$round}", $user['id']);

        jsonResponse([
            'success' => true,
            'message' => 'Interview preparation note saved successfully!'
        ], 201);
        break;

    case 'delete':
        $appId = intval($input['application_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ? AND student_id = ?");
        $stmt->execute([$appId, $studentId]);

        if ($stmt->rowCount() > 0) {
            logActivity('APP_DELETED', "Removed application #{$appId} from tracker", $user['id']);
            jsonResponse(['success' => true, 'message' => 'Application removed from tracker.']);
        } else {
            jsonError('Application not found or unauthorized', 404);
        }
        break;

    case 'export_csv':
        // Export applications to CSV
        $sql = "SELECT a.id,
                       CASE WHEN a.is_external = 1 THEN a.external_company ELSE cp.company_name END AS company_name,
                       CASE WHEN a.is_external = 1 THEN a.external_title ELSE j.title END AS role_title,
                       a.status,
                       a.applied_at,
                       a.interview_date,
                       a.salary_offered
                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
                LEFT JOIN company_profiles cp ON j.company_id = cp.id
                WHERE a.student_id = ?
                ORDER BY a.applied_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="careerpulse_applications_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Application ID', 'Company', 'Role Title', 'Current Status', 'Applied Date', 'Interview Schedule', 'Salary/Stipend Offered']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['company_name'],
                $r['role_title'],
                $r['status'],
                $r['applied_at'],
                $r['interview_date'] ?? 'N/A',
                $r['salary_offered'] ?? 'N/A'
            ]);
        }
        fclose($out);
        exit;

    case 'export_ics':
        // Export interviews and deadlines to standard .ics calendar
        $sql = "SELECT a.id, a.interview_date,
                       CASE WHEN a.is_external = 1 THEN a.external_company ELSE cp.company_name END AS company_name,
                       CASE WHEN a.is_external = 1 THEN a.external_title ELSE j.title END AS role_title,
                       j.deadline
                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
                LEFT JOIN company_profiles cp ON j.company_id = cp.id
                WHERE a.student_id = ? AND (a.interview_date IS NOT NULL OR j.deadline IS NOT NULL)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $events = $stmt->fetchAll();

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="careerpulse_schedule.ics"');

        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//CareerPulse//Job Tracker Schedule//EN\r\n";
        echo "CALSCALE:GREGORIAN\r\n";

        foreach ($events as $ev) {
            if (!empty($ev['interview_date'])) {
                $start = date('Ymd\THis', strtotime($ev['interview_date']));
                $end = date('Ymd\THis', strtotime($ev['interview_date'] . ' +1 hour'));
                echo "BEGIN:VEVENT\r\n";
                echo "UID:interview-{$ev['id']}@careerpulse\r\n";
                echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
                echo "DTSTART:{$start}\r\n";
                echo "DTEND:{$end}\r\n";
                echo "SUMMARY:Interview with {$ev['company_name']} ({$ev['role_title']})\r\n";
                echo "DESCRIPTION:CareerPulse Scheduled Interview Round\r\n";
                echo "STATUS:CONFIRMED\r\n";
                echo "END:VEVENT\r\n";
            }
            if (!empty($ev['deadline'])) {
                $dStart = date('Ymd', strtotime($ev['deadline']));
                echo "BEGIN:VEVENT\r\n";
                echo "UID:deadline-{$ev['id']}@careerpulse\r\n";
                echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
                echo "DTSTART;VALUE=DATE:{$dStart}\r\n";
                echo "SUMMARY:Application Deadline: {$ev['company_name']} - {$ev['role_title']}\r\n";
                echo "DESCRIPTION:Application closes today on CareerPulse\r\n";
                echo "END:VEVENT\r\n";
            }
        }
        echo "END:VCALENDAR\r\n";
        exit;

    default:
        jsonError('Unknown tracker action: ' . htmlspecialchars($action));
}
