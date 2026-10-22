<?php
// CareerPulse - Job Portal & Application Management API
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list_jobs';
$pdo = getDB();
$currentUser = getCurrentUser();

$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true) ?: [];
$input = array_merge($_POST, $jsonBody);

switch ($action) {
    case 'list_jobs':
        // Public or authenticated list of approved jobs
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $workplace = trim($_GET['workplace'] ?? '');
        $minCgpa = isset($_GET['max_min_cgpa']) ? floatval($_GET['max_min_cgpa']) : 0;
        $sort = $_GET['sort'] ?? 'newest';

        $conditions = ["j.status = 'approved'"];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(j.title LIKE ? OR cp.company_name LIKE ? OR j.skills_required LIKE ? OR j.location LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($category) && $category !== 'All') {
            $conditions[] = "j.category = ?";
            $params[] = $category;
        }

        if (!empty($type) && $type !== 'All') {
            $conditions[] = "j.type = ?";
            $params[] = $type;
        }

        if (!empty($workplace) && $workplace !== 'All') {
            $conditions[] = "j.workplace_type = ?";
            $params[] = $workplace;
        }

        $whereClause = implode(' AND ', $conditions);
        $orderClause = "j.created_at DESC";
        if ($sort === 'deadline_soon') {
            $orderClause = "j.deadline ASC";
        } elseif ($sort === 'cgpa_asc') {
            $orderClause = "j.min_cgpa ASC";
        }

        $sql = "SELECT j.*, cp.company_name, cp.logo_url, cp.website, cp.industry,
                       (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as total_applicants
                FROM jobs j
                JOIN company_profiles cp ON j.company_id = cp.id
                WHERE $whereClause
                ORDER BY $orderClause";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll();

        // If user is a logged-in student, compute eligibility and applied status!
        $studentAppliedMap = [];
        $studentProfile = null;

        if ($currentUser && $currentUser['role'] === 'student') {
            $studentProfile = $currentUser;
            $appStmt = $pdo->prepare("SELECT job_id, status FROM applications WHERE student_id = ? AND job_id IS NOT NULL");
            $appStmt->execute([$currentUser['student_id']]);
            $rows = $appStmt->fetchAll();
            foreach ($rows as $r) {
                $studentAppliedMap[$r['job_id']] = $r['status'];
            }
        }

        $now = new DateTime();
        foreach ($jobs as &$job) {
            // Deadline countdown calculation
            $deadlineDate = new DateTime($job['deadline']);
            $diff = $now->diff($deadlineDate);
            $isPast = $deadlineDate < $now;
            $daysLeft = $isPast ? -1 : ($diff->days + ($diff->h > 0 ? 1 : 0));
            $job['days_left'] = $daysLeft;
            $job['is_expired'] = $isPast;

            // Check student status
            if ($studentProfile) {
                $job['has_applied'] = isset($studentAppliedMap[$job['id']]);
                $job['my_status'] = $studentAppliedMap[$job['id']] ?? null;

                // Eligibility check
                $cgpaMet = $studentProfile['cgpa'] >= $job['min_cgpa'];
                $branchList = array_map('trim', explode(',', strtolower($job['eligible_branches'])));
                $studentDept = strtolower($studentProfile['department'] ?? '');
                $branchMet = empty($job['eligible_branches']) || in_array('any graduate', $branchList) || in_array($studentDept, $branchList) || strpos(implode(' ', $branchList), $studentDept) !== false;

                $job['eligibility'] = [
                    'is_eligible' => $cgpaMet && $branchMet,
                    'cgpa_met' => $cgpaMet,
                    'branch_met' => $branchMet,
                    'student_cgpa' => $studentProfile['cgpa'],
                    'min_cgpa' => $job['min_cgpa'],
                    'student_branch' => $studentProfile['department'],
                    'reason' => !$cgpaMet 
                        ? "Needs {$job['min_cgpa']} CGPA, you have {$studentProfile['cgpa']}" 
                        : (!$branchMet ? "Eligible branches: {$job['eligible_branches']}" : "You're fully eligible!")
                ];

                // Keyword match score
                $skillsReq = array_filter(array_map('trim', explode(',', strtolower($job['skills_required'] ?? ''))));
                $studentSkills = array_filter(array_map('trim', explode(',', strtolower($studentProfile['skills'] ?? ''))));
                $matches = [];
                if (!empty($skillsReq) && !empty($studentSkills)) {
                    foreach ($skillsReq as $req) {
                        foreach ($studentSkills as $sSkill) {
                            if (strpos($sSkill, $req) !== false || strpos($req, $sSkill) !== false) {
                                $matches[] = $req;
                                break;
                            }
                        }
                    }
                    $matchScore = count($skillsReq) > 0 ? round((count($matches) / count($skillsReq)) * 100) : 0;
                } else {
                    $matchScore = 50; // default baseline
                }
                $job['match_score'] = min(100, $matchScore);
                $job['matched_skills'] = array_unique($matches);
            } else {
                $job['has_applied'] = false;
                $job['eligibility'] = null;
                $job['match_score'] = null;
            }
        }

        jsonResponse([
            'success' => true,
            'count' => count($jobs),
            'jobs' => $jobs
        ]);
        break;

    case 'get_job':
        $jobId = intval($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT j.*, cp.company_name, cp.logo_url, cp.website, cp.industry, cp.location AS company_hq, cp.description AS company_desc
                               FROM jobs j
                               JOIN company_profiles cp ON j.company_id = cp.id
                               WHERE j.id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            jsonError('Job vacancy not found.', 404);
        }

        if ($currentUser && $currentUser['role'] === 'student') {
            $appStmt = $pdo->prepare("SELECT * FROM applications WHERE job_id = ? AND student_id = ?");
            $appStmt->execute([$jobId, $currentUser['student_id']]);
            $job['my_application'] = $appStmt->fetch() ?: null;
        }

        jsonResponse([
            'success' => true,
            'job' => $job
        ]);
        break;

    case 'apply':
        $user = requireRole('student');
        $jobId = intval($input['job_id'] ?? 0);
        $coverNote = trim($input['cover_note'] ?? '');

        // Check if job exists and is approved
        $stmt = $pdo->prepare("SELECT j.*, cp.user_id as company_user_id, cp.company_name FROM jobs j JOIN company_profiles cp ON j.company_id = cp.id WHERE j.id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job || $job['status'] !== 'approved') {
            jsonError('This job posting is not open for applications.', 400);
        }

        // Check deadline
        if (strtotime($job['deadline']) < strtotime(date('Y-m-d'))) {
            jsonError('Application deadline has expired for this position.', 400);
        }

        // Check if already applied
        $stmt = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND student_id = ?");
        $stmt->execute([$jobId, $user['student_id']]);
        if ($stmt->fetch()) {
            jsonError('You have already submitted an application for this position.');
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, resume_attached) VALUES (?, ?, 0, 'Applied', ?, ?)");
            $stmt->execute([$jobId, $user['student_id'], $coverNote, $user['resume_filename']]);
            $appId = $pdo->lastInsertId();

            // Record initial history
            $hStmt = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status, notes, changed_by_user_id) VALUES (?, NULL, 'Applied', 'Direct application submitted through CareerPulse portal', ?)");
            $hStmt->execute([$appId, $user['id']]);

            // Notify student
            createNotification($user['id'], 'Application Submitted', "You successfully applied for {$job['title']} at {$job['company_name']}.", 'success', '#tracker');

            // Notify company recruiter
            createNotification($job['company_user_id'], 'New Applicant Received', "{$user['name']} has applied for {$job['title']}.", 'info', '#applicants');

            // Log activity
            logActivity('JOB_APPLIED', "Applied to {$job['company_name']} - {$job['title']}", $user['id']);

            $pdo->commit();

            jsonResponse([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'application_id' => $appId
            ], 201);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to submit application: ' . $e->getMessage(), 500);
        }
        break;

    case 'company_jobs':
        $user = requireRole('company');
        $companyId = $user['company_id'];

        $sql = "SELECT j.*,
                       (SELECT COUNT(*) FROM applications WHERE job_id = j.id) AS total_applicants,
                       (SELECT COUNT(*) FROM applications WHERE job_id = j.id AND status = 'Interviewing') AS interviewing_count,
                       (SELECT COUNT(*) FROM applications WHERE job_id = j.id AND status = 'Offered') AS offered_count
                FROM jobs j
                WHERE j.company_id = ?
                ORDER BY j.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$companyId]);
        $jobs = $stmt->fetchAll();

        // Calculate company stats
        $totalApps = 0;
        $totalInterviews = 0;
        $totalOffers = 0;
        foreach ($jobs as $j) {
            $totalApps += $j['total_applicants'];
            $totalInterviews += $j['interviewing_count'];
            $totalOffers += $j['offered_count'];
        }

        jsonResponse([
            'success' => true,
            'jobs' => $jobs,
            'analytics' => [
                'total_vacancies' => count($jobs),
                'total_applicants' => $totalApps,
                'interviewing' => $totalInterviews,
                'hires_offered' => $totalOffers
            ]
        ]);
        break;

    case 'create_job':
        $user = requireRole('company');
        $companyId = $user['company_id'];

        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? 'Software Engineering');
        $type = trim($input['type'] ?? 'Full-time');
        $workplace = trim($input['workplace_type'] ?? 'Hybrid');
        $location = trim($input['location'] ?? 'Bengaluru, India');
        $salary = trim($input['salary_range'] ?? '₹8,00,000 - ₹12,00,000 / yr');
        $minCgpa = floatval($input['min_cgpa'] ?? 7.00);
        $branches = trim($input['eligible_branches'] ?? 'Computer Science, Information Technology');
        $years = trim($input['eligible_years'] ?? '2025, 2026, 2027');
        $deadline = trim($input['deadline'] ?? date('Y-m-d', strtotime('+30 days')));
        $desc = trim($input['description'] ?? '');
        $reqs = trim($input['requirements'] ?? '');
        $skills = trim($input['skills_required'] ?? '');

        if (empty($title) || empty($desc) || empty($reqs)) {
            jsonError('Job title, description, and requirements are required.');
        }

        // Job goes to 'approved' if company is verified, or 'pending' if unverified
        $initialStatus = $user['is_verified'] ? 'approved' : 'pending';

        $stmt = $pdo->prepare("INSERT INTO jobs (company_id, title, category, type, workplace_type, location, salary_range, min_cgpa, eligible_branches, eligible_years, deadline, description, requirements, skills_required, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $companyId, $title, $category, $type, $workplace, $location, $salary,
            $minCgpa, $branches, $years, $deadline, $desc, $reqs, $skills, $initialStatus
        ]);
        $newJobId = $pdo->lastInsertId();

        logActivity('JOB_POSTED', "Posted new vacancy: {$title}", $user['id']);

        // Notify Admin for review
        $adminStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $adminUser = $adminStmt->fetch();
        if ($adminUser) {
            createNotification($adminUser['id'], 'New Drive Submitted', "{$user['company_name']} posted: {$title}", 'info', '#moderation');
        }

        jsonResponse([
            'success' => true,
            'message' => 'Job vacancy posted successfully!' . ($initialStatus === 'pending' ? ' Awaiting admin approval.' : ''),
            'job_id' => $newJobId
        ], 201);
        break;

    case 'update_job':
        $user = requireRole('company');
        $jobId = intval($input['job_id'] ?? 0);

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM jobs WHERE id = ? AND company_id = ?");
        $stmt->execute([$jobId, $user['company_id']]);
        if (!$stmt->fetch()) {
            jsonError('Job not found or unauthorized', 404);
        }

        $title = trim($input['title'] ?? '');
        $category = trim($input['category'] ?? '');
        $type = trim($input['type'] ?? '');
        $workplace = trim($input['workplace_type'] ?? '');
        $location = trim($input['location'] ?? '');
        $salary = trim($input['salary_range'] ?? '');
        $minCgpa = floatval($input['min_cgpa'] ?? 7.00);
        $branches = trim($input['eligible_branches'] ?? '');
        $years = trim($input['eligible_years'] ?? '');
        $deadline = trim($input['deadline'] ?? '');
        $desc = trim($input['description'] ?? '');
        $reqs = trim($input['requirements'] ?? '');
        $skills = trim($input['skills_required'] ?? '');

        $stmt = $pdo->prepare("UPDATE jobs SET title = ?, category = ?, type = ?, workplace_type = ?, location = ?, salary_range = ?, min_cgpa = ?, eligible_branches = ?, eligible_years = ?, deadline = ?, description = ?, requirements = ?, skills_required = ? WHERE id = ?");
        $stmt->execute([$title, $category, $type, $workplace, $location, $salary, $minCgpa, $branches, $years, $deadline, $desc, $reqs, $skills, $jobId]);

        logActivity('JOB_UPDATED', "Updated job vacancy #{$jobId}", $user['id']);

        jsonResponse(['success' => true, 'message' => 'Job posting updated successfully!']);
        break;

    case 'job_applicants':
        $user = requireRole('company');
        $jobId = intval($_GET['job_id'] ?? 0);

        // Verify company owns this job
        $stmt = $pdo->prepare("SELECT j.* FROM jobs j WHERE j.id = ? AND j.company_id = ?");
        $stmt->execute([$jobId, $user['company_id']]);
        $job = $stmt->fetch();

        if (!$job) {
            jsonError('Job not found or unauthorized', 404);
        }

        $sql = "SELECT a.*, 
                       u.id AS student_user_id, u.name AS student_name, u.email AS student_email,
                       sp.phone, sp.department, sp.cgpa, sp.graduation_year, sp.skills, sp.resume_filename, sp.github_url, sp.linkedin_url
                FROM applications a
                JOIN student_profiles sp ON a.student_id = sp.id
                JOIN users u ON sp.user_id = u.id
                WHERE a.job_id = ?
                ORDER BY a.applied_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$jobId]);
        $applicants = $stmt->fetchAll();

        // Calculate match percentage for each applicant
        $skillsReq = array_filter(array_map('trim', explode(',', strtolower($job['skills_required'] ?? ''))));
        foreach ($applicants as &$app) {
            $studentSkills = array_filter(array_map('trim', explode(',', strtolower($app['skills'] ?? ''))));
            $matches = [];
            if (!empty($skillsReq) && !empty($studentSkills)) {
                foreach ($skillsReq as $req) {
                    foreach ($studentSkills as $sSkill) {
                        if (strpos($sSkill, $req) !== false || strpos($req, $sSkill) !== false) {
                            $matches[] = $req;
                            break;
                        }
                    }
                }
                $app['match_percentage'] = count($skillsReq) > 0 ? round((count($matches) / count($skillsReq)) * 100) : 50;
            } else {
                $app['match_percentage'] = 60;
            }
        }

        jsonResponse([
            'success' => true,
            'job' => $job,
            'applicants' => $applicants
        ]);
        break;

    case 'update_applicant_status':
        $user = requireRole('company');
        $appId = intval($input['application_id'] ?? 0);
        $newStatus = trim($input['status'] ?? '');
        $remarks = trim($input['remarks'] ?? '');
        $interviewDate = !empty($input['interview_date']) ? $input['interview_date'] : null;
        $salaryOffered = trim($input['salary_offered'] ?? '');

        $allowed = ['Applied', 'Screening', 'Interviewing', 'Offered', 'Rejected'];
        if (!in_array($newStatus, $allowed)) {
            jsonError('Invalid applicant status.');
        }

        // Verify company owns this application via job
        $stmt = $pdo->prepare("SELECT a.*, j.title, cp.company_name, u.id AS student_user_id, u.name AS student_name
                               FROM applications a
                               JOIN jobs j ON a.job_id = j.id
                               JOIN company_profiles cp ON j.company_id = cp.id
                               JOIN student_profiles sp ON a.student_id = sp.id
                               JOIN users u ON sp.user_id = u.id
                               WHERE a.id = ? AND cp.id = ?");
        $stmt->execute([$appId, $user['company_id']]);
        $app = $stmt->fetch();

        if (!$app) {
            jsonError('Application not found or unauthorized', 404);
        }

        $oldStatus = $app['status'];

        $pdo->beginTransaction();
        try {
            $uStmt = $pdo->prepare("UPDATE applications SET status = ?, interview_date = ?, salary_offered = ?, updated_at = NOW() WHERE id = ?");
            $uStmt->execute([$newStatus, $interviewDate, $salaryOffered, $appId]);

            $hStmt = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status, notes, changed_by_user_id) VALUES (?, ?, ?, ?, ?)");
            $noteText = $remarks ?: "Status updated by {$user['company_name']} recruiter to {$newStatus}";
            $hStmt->execute([$appId, $oldStatus, $newStatus, $noteText, $user['id']]);

            // Notify the student
            $notifType = ($newStatus === 'Offered') ? 'success' : (($newStatus === 'Rejected') ? 'danger' : 'info');
            $notifMsg = "{$user['company_name']} updated your application for '{$app['title']}' to: {$newStatus}." . ($interviewDate ? " Interview Date: $interviewDate" : "");
            createNotification($app['student_user_id'], "Application Update: {$app['title']}", $notifMsg, $notifType, '#tracker');

            logActivity('CANDIDATE_STATUS_CHANGED', "{$user['company_name']} moved {$app['student_name']} to {$newStatus} for {$app['title']}", $user['id']);

            $pdo->commit();

            jsonResponse([
                'success' => true,
                'message' => "Candidate moved to {$newStatus} successfully!"
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            jsonError('Failed to update candidate status: ' . $e->getMessage(), 500);
        }
        break;

    default:
        jsonError('Unknown portal action: ' . htmlspecialchars($action));
}
