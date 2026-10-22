<?php
// CareerPulse - Intelligent Resume Keyword Matcher & Gap Analyzer
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$user = requireRole('student');
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: $_POST;

$jobText = strtolower($input['job_description'] ?? '');
$customSkills = strtolower($input['student_skills'] ?? ($user['skills'] ?? ''));

if (empty($jobText)) {
    jsonError('Please paste or provide a job description to analyze.');
}

// Dictionary of high-value industry tech & professional keywords
$techKeywords = [
    'python', 'javascript', 'typescript', 'java', 'c++', 'c#', 'php', 'golang', 'ruby', 'rust', 'swift', 'kotlin',
    'html', 'html5', 'css', 'css3', 'sass', 'tailwind', 'bootstrap', 'react', 'react.js', 'vue', 'vue.js', 'angular', 'next.js',
    'node.js', 'express', 'django', 'flask', 'spring', 'laravel', 'fastapi',
    'sql', 'mysql', 'postgresql', 'mongodb', 'redis', 'sqlite', 'nosql', 'dynamodb',
    'aws', 'azure', 'gcp', 'google cloud', 'docker', 'kubernetes', 'ci/cd', 'git', 'github', 'linux', 'bash',
    'machine learning', 'deep learning', 'nlp', 'computer vision', 'pytorch', 'tensorflow', 'scikit-learn', 'pandas', 'numpy',
    'rest api', 'graphql', 'microservices', 'distributed systems', 'agile', 'scrum', 'system design',
    'unit testing', 'jest', 'cypress', 'figma', 'ui/ux', 'security', 'owasp'
];

// Extract found keywords in the job description
$foundInJob = [];
foreach ($techKeywords as $kw) {
    if (preg_match('/\b' . preg_quote($kw, '/') . '\b/i', $jobText)) {
        $foundInJob[] = $kw;
    }
}

// Extract student's skills
$studentSkillsList = array_filter(array_map('trim', explode(',', $customSkills)));

// Match analysis
$matched = [];
$missing = [];

foreach ($foundInJob as $req) {
    $hasSkill = false;
    foreach ($studentSkillsList as $s) {
        if (strpos($s, $req) !== false || strpos($req, $s) !== false) {
            $hasSkill = true;
            break;
        }
    }
    if ($hasSkill) {
        $matched[] = $req;
    } else {
        $missing[] = $req;
    }
}

$totalKeywords = count($foundInJob);
$matchCount = count($matched);
$percentage = $totalKeywords > 0 ? round(($matchCount / $totalKeywords) * 100) : 75;

// Recommendation feedback
if ($percentage >= 80) {
    $verdict = "Outstanding Match! Your skillset strongly aligns with this job description. You have high probability of passing ATS screening.";
    $level = "High";
} elseif ($percentage >= 50) {
    $verdict = "Solid Match! You meet the core requirements. Consider highlighting the missing keywords in your project bullet points.";
    $level = "Medium";
} else {
    $verdict = "Skill Gap Detected. This role emphasizes technologies that are currently not highlighted in your profile.";
    $level = "Moderate";
}

jsonResponse([
    'success' => true,
    'match_percentage' => $percentage,
    'match_level' => $level,
    'total_detected_keywords' => $totalKeywords,
    'matched_keywords' => array_values(array_unique($matched)),
    'missing_keywords' => array_values(array_unique($missing)),
    'verdict' => $verdict
]);
