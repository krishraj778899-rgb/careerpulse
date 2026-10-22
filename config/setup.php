<?php
// CareerPulse Database Setup & Seeder
// Executes schema and populates rich demo data for Students, Companies, and Admin

function runDatabaseSetup($pdo, $forceReset = false) {
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `careerpulse_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `careerpulse_db`");

        if ($forceReset) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $tables = ['activity_logs', 'notifications', 'interview_notes', 'application_history', 'applications', 'jobs', 'company_profiles', 'student_profiles', 'users'];
            foreach ($tables as $tbl) {
                $pdo->exec("DROP TABLE IF EXISTS `$tbl`");
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }

        // Read and execute schema
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }

        // Check if users exist. If not, seed demo data!
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();

        if ($userCount == 0) {
            seedDemoData($pdo);
        }

        return ['success' => true, 'message' => 'Database initialized and seeded successfully.'];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function seedDemoData($pdo) {
    // Passwords hashed with bcrypt
    $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
    $companyPass = password_hash('company123', PASSWORD_BCRYPT);
    $studentPass = password_hash('student123', PASSWORD_BCRYPT);

    // 1. Insert Admin
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'admin', 'active')");
    $stmt->execute(['Dr. Arthur Vance (Placement Dean)', 'admin@careerpulse.com', $adminPass]);
    $adminId = $pdo->lastInsertId();

    // 2. Insert Companies
    $companies = [
        [
            'name' => 'Stripe Talent Team',
            'email' => 'recruiter@stripe.com',
            'company_name' => 'Stripe',
            'website' => 'https://stripe.com',
            'industry' => 'Fintech & Payments',
            'location' => 'Bengaluru, India (Hybrid)',
            'logo' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=120&auto=format&fit=crop&q=80',
            'desc' => 'Financial infrastructure platform for the internet. Millions of companies use Stripe to accept payments and grow their businesses.'
        ],
        [
            'name' => 'Google University Programs',
            'email' => 'careers@google.com',
            'company_name' => 'Google',
            'website' => 'https://careers.google.com',
            'industry' => 'Cloud & Artificial Intelligence',
            'location' => 'Hyderabad, India (Hybrid)',
            'logo' => 'https://images.unsplash.com/photo-1572021335469-31706a17aaef?w=120&auto=format&fit=crop&q=80',
            'desc' => 'Organizing the world’s information and making it universally accessible and useful. Innovating at scale across Search, Cloud, and Gemini.'
        ],
        [
            'name' => 'Microsoft Campus Hiring',
            'email' => 'jobs@microsoft.com',
            'company_name' => 'Microsoft',
            'website' => 'https://careers.microsoft.com',
            'industry' => 'Enterprise Software & Cloud',
            'location' => 'Bengaluru, India (Hybrid)',
            'logo' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=120&auto=format&fit=crop&q=80',
            'desc' => 'Empowering every person and every organization on the planet to achieve more with Azure, Office, and GitHub.'
        ],
        [
            'name' => 'Amazon University Recruiting',
            'email' => 'talent@amazon.com',
            'company_name' => 'Amazon',
            'website' => 'https://amazon.jobs',
            'industry' => 'E-Commerce & AWS',
            'location' => 'Chennai, India (On-site)',
            'logo' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=120&auto=format&fit=crop&q=80',
            'desc' => 'Earth’s most customer-centric company, focused on pioneering cloud services, logistics, and retail tech.'
        ],
        [
            'name' => 'Razorpay Campus Relations',
            'email' => 'hiring@razorpay.com',
            'company_name' => 'Razorpay',
            'website' => 'https://razorpay.com',
            'industry' => 'Fintech',
            'location' => 'Bengaluru, India (Remote)',
            'logo' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=120&auto=format&fit=crop&q=80',
            'desc' => 'India’s first full-stack financial solutions company enabling seamless digital transactions for millions of businesses.'
        ]
    ];

    $companyMap = []; // company_name => company_profile_id
    foreach ($companies as $c) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'company', 'active')");
        $stmt->execute([$c['name'], $c['email'], $companyPass]);
        $uid = $pdo->lastInsertId();

        $cStmt = $pdo->prepare("INSERT INTO company_profiles (user_id, company_name, website, industry, location, logo_url, description, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $cStmt->execute([$uid, $c['company_name'], $c['website'], $c['industry'], $c['location'], $c['logo'], $c['desc']]);
        $companyMap[$c['company_name']] = $pdo->lastInsertId();
    }

    // 3. Insert Students
    $students = [
        [
            'name' => 'Alex Rivera',
            'email' => 'alex@university.edu',
            'phone' => '+91 98765 43210',
            'department' => 'Computer Science',
            'cgpa' => 8.75,
            'year' => 2026,
            'skills' => 'React, JavaScript, TypeScript, PHP, MySQL, Node.js, Python, Git, Docker, REST APIs, Tailwind CSS',
            'bio' => 'Full-stack software enthusiast passionate about building scalable web applications and high-throughput microservices.',
            'github' => 'https://github.com/alexrivera-demo',
            'linkedin' => 'https://linkedin.com/in/alexrivera-demo'
        ],
        [
            'name' => 'Priya Sharma',
            'email' => 'priya@university.edu',
            'phone' => '+91 98765 43211',
            'department' => 'Information Technology',
            'cgpa' => 7.40,
            'year' => 2026,
            'skills' => 'Python, Django, SQL, Data Analysis, Machine Learning, Tableau, Pandas, Git',
            'bio' => 'Aspiring Data Engineer and Machine Learning practitioner interested in data pipelines and predictive modeling.',
            'github' => 'https://github.com/priyasharma-demo',
            'linkedin' => 'https://linkedin.com/in/priyasharma-demo'
        ],
        [
            'name' => 'Rohan Mehta',
            'email' => 'rohan@university.edu',
            'phone' => '+91 98765 43212',
            'department' => 'Electronics & Communication',
            'cgpa' => 9.20,
            'year' => 2025,
            'skills' => 'C++, Embedded Systems, Python, IoT, RTOS, Verilog, Linux, Computer Vision',
            'bio' => 'Embedded systems and edge computing researcher building high-performance low-latency firmware.',
            'github' => 'https://github.com/rohanmehta-demo',
            'linkedin' => 'https://linkedin.com/in/rohanmehta-demo'
        ],
        [
            'name' => 'Ananya Iyer',
            'email' => 'ananya@university.edu',
            'phone' => '+91 98765 43213',
            'department' => 'Computer Science',
            'cgpa' => 8.10,
            'year' => 2026,
            'skills' => 'UI/UX Design, Figma, HTML5, CSS3, JavaScript, Vue.js, User Research',
            'bio' => 'Product designer bridging human-centered interaction design with clean, accessible frontend implementations.',
            'github' => 'https://github.com/ananyaiyer-demo',
            'linkedin' => 'https://linkedin.com/in/ananyaiyer-demo'
        ]
    ];

    $studentMap = []; // email => student_profile_id
    foreach ($students as $s) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, status) VALUES (?, ?, ?, 'student', 'active')");
        $stmt->execute([$s['name'], $s['email'], $studentPass]);
        $uid = $pdo->lastInsertId();

        $sStmt = $pdo->prepare("INSERT INTO student_profiles (user_id, phone, department, cgpa, graduation_year, skills, bio, github_url, linkedin_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $sStmt->execute([$uid, $s['phone'], $s['department'], $s['cgpa'], $s['year'], $s['skills'], $s['bio'], $s['github'], $s['linkedin']]);
        $studentMap[$s['email']] = $pdo->lastInsertId();
    }

    // 4. Insert Job Postings / Drives (with varying deadlines: 2 days, 5 days, 15 days, 30 days)
    $today = date('Y-m-d');
    $d2 = date('Y-m-d', strtotime('+2 days'));
    $d5 = date('Y-m-d', strtotime('+5 days'));
    $d14 = date('Y-m-d', strtotime('+14 days'));
    $d25 = date('Y-m-d', strtotime('+25 days'));

    $jobs = [
        [
            'company' => 'Stripe',
            'title' => 'Software Engineer Intern (Backend & Payments)',
            'category' => 'Software Engineering',
            'type' => 'Internship',
            'workplace' => 'Hybrid',
            'location' => 'Bengaluru, India',
            'salary' => '₹1,25,000 / month + Relocation',
            'min_cgpa' => 7.50,
            'branches' => 'Computer Science, Information Technology, Electronics',
            'years' => '2026, 2027',
            'deadline' => $d2, // Urgent deadline!
            'desc' => "Join Stripe's Core Payments Infrastructure team. You will build and scale distributed services that process hundreds of billions of dollars annually for global enterprises.",
            'reqs' => "• Strong proficiency in at least one modern backend language (Go, Java, Python, or Ruby).\n• Solid understanding of relational databases (PostgreSQL/MySQL), ACID properties, and API design.\n• Problem-solving mindset with data structures and algorithmic complexity.",
            'skills' => 'Python, Go, Java, MySQL, REST APIs, Git, Distributed Systems',
            'status' => 'approved'
        ],
        [
            'company' => 'Google',
            'title' => 'Associate Cloud Engineer (Full-Time)',
            'category' => 'Cloud & DevOps',
            'type' => 'Full-time',
            'workplace' => 'Hybrid',
            'location' => 'Hyderabad, India',
            'salary' => '₹24,00,000 - ₹32,00,000 / yr',
            'min_cgpa' => 8.00,
            'branches' => 'Computer Science, Information Technology',
            'years' => '2025, 2026',
            'deadline' => $d5,
            'desc' => "As an Associate Cloud Engineer at Google Cloud, you will deploy enterprise architectures, automate continuous integration, and optimize infrastructure security and scalability.",
            'reqs' => "• Solid understanding of Linux fundamentals, networking (TCP/IP, DNS, HTTP/S), and container technologies.\n• Experience with Docker, Kubernetes, and scripting with Python or Bash.\n• Minimum 8.0 CGPA throughout academic career with zero active backlogs.",
            'skills' => 'Linux, Docker, Kubernetes, Python, Cloud, Networking, Git',
            'status' => 'approved'
        ],
        [
            'company' => 'Microsoft',
            'title' => 'Software Development Engineer I (Azure & Core OS)',
            'category' => 'Software Engineering',
            'type' => 'Full-time',
            'workplace' => 'Hybrid',
            'location' => 'Bengaluru, India',
            'salary' => '₹20,00,000 - ₹26,00,000 / yr',
            'min_cgpa' => 7.00,
            'branches' => 'Computer Science, Information Technology, Electronics, Mechanical',
            'years' => '2025, 2026',
            'deadline' => $d14,
            'desc' => "Microsoft SDEs build software at planetary scale. You will work on next-generation cloud services, high-performance computing, and collaborative developer platforms.",
            'reqs' => "• Strong foundational programming skills in C++, C#, Java, or Python.\n• Good understanding of OS concepts, multithreading, concurrency, and memory management.\n• Excellent communication and cross-team collaboration.",
            'skills' => 'C++, C#, Java, Data Structures, Algorithms, OS, Git',
            'status' => 'approved'
        ],
        [
            'company' => 'Razorpay',
            'title' => 'Frontend Engineer Intern (React / Design Systems)',
            'category' => 'Design & Frontend',
            'type' => 'Internship',
            'workplace' => 'Remote',
            'location' => 'Remote (India)',
            'salary' => '₹45,000 / month',
            'min_cgpa' => 6.50,
            'branches' => 'Computer Science, Information Technology, Electronics, Any Graduate',
            'years' => '2026, 2027',
            'deadline' => $d25,
            'desc' => "Design and engineer frictionless checkouts and financial dashboards utilized by 8+ million businesses across India.",
            'reqs' => "• Experience with modern JavaScript (ES6+), React.js, and CSS preprocessors/Tailwind.\n• Passion for accessible UI/UX, micro-interactions, and component modularity.\n• Familiarity with Git workflows and browser performance profiling.",
            'skills' => 'React, JavaScript, TypeScript, Tailwind CSS, HTML5, CSS3, Git',
            'status' => 'approved'
        ],
        [
            'company' => 'Amazon',
            'title' => 'Graduate Data Analyst (Operations & Supply Chain)',
            'category' => 'Data Science & Analytics',
            'type' => 'Full-time',
            'workplace' => 'On-site',
            'location' => 'Chennai, India',
            'salary' => '₹14,00,000 - ₹18,00,000 / yr',
            'min_cgpa' => 7.20,
            'branches' => 'Computer Science, Information Technology, Electrical, Mechanical',
            'years' => '2025, 2026',
            'deadline' => $d14,
            'desc' => "Analyze complex multi-million row datasets to optimize fulfilment center logistics, delivery turnaround times, and inventory prediction.",
            'reqs' => "• Advanced SQL proficiency (window functions, CTEs, query optimization).\n• Hands-on experience with Python (Pandas/NumPy) and Business Intelligence tools (PowerBI/Tableau).\n• Ability to present data-driven narratives to senior operational leaders.",
            'skills' => 'SQL, Python, Pandas, Tableau, Data Analysis, Statistics, Excel',
            'status' => 'approved'
        ],
        [
            'company' => 'Google',
            'title' => 'Machine Learning Research Intern (Gemini & Multimodal)',
            'category' => 'Artificial Intelligence',
            'type' => 'Internship',
            'workplace' => 'Hybrid',
            'location' => 'Bengaluru, India',
            'salary' => '₹1,50,000 / month + Benefits',
            'min_cgpa' => 8.50, // High CGPA requirement for testing eligibility!
            'branches' => 'Computer Science, Electronics',
            'years' => '2026',
            'deadline' => $d14,
            'desc' => "Conduct experiments in frontier multimodal modeling, synthetic data curation, and reasoning distillation alongside Google DeepMind researchers.",
            'reqs' => "• Deep proficiency in PyTorch/JAX and foundational transformer architectures.\n• Publications or open-source projects in NLP, vision, or reinforcement learning.\n• Minimum 8.5 CGPA.",
            'skills' => 'Python, PyTorch, Machine Learning, Deep Learning, NLP, Git',
            'status' => 'approved'
        ],
        [
            'company' => 'Stripe',
            'title' => 'Product Security Engineer (Fintech Compliance)',
            'category' => 'Cybersecurity',
            'type' => 'Full-time',
            'workplace' => 'Hybrid',
            'location' => 'Bengaluru, India',
            'salary' => '₹22,00,000 - ₹28,00,000 / yr',
            'min_cgpa' => 7.00,
            'branches' => 'Computer Science, Information Technology',
            'years' => '2025, 2026',
            'deadline' => $d25,
            'desc' => "Hunt vulnerabilities, conduct code reviews, and engineer automated security controls protecting millions of internet businesses.",
            'reqs' => "• Knowledge of web security principles (OWASP Top 10, cryptographic primitives, auth protocols).\n• Scripting fluency in Python, Ruby, or Go.\n• Experience with penetration testing and security tooling.",
            'skills' => 'Security, Python, Cryptography, OWASP, Linux, Networking',
            'status' => 'pending' // Pending moderation for Admin dashboard!
        ]
    ];

    $jobIds = [];
    foreach ($jobs as $j) {
        $cId = $companyMap[$j['company']];
        $jStmt = $pdo->prepare("INSERT INTO jobs (company_id, title, category, type, workplace_type, location, salary_range, min_cgpa, eligible_branches, eligible_years, deadline, description, requirements, skills_required, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $jStmt->execute([
            $cId, $j['title'], $j['category'], $j['type'], $j['workplace'], $j['location'],
            $j['salary'], $j['min_cgpa'], $j['branches'], $j['years'], $j['deadline'],
            $j['desc'], $j['reqs'], $j['skills'], $j['status']
        ]);
        $jobIds[] = $pdo->lastInsertId();
    }

    // 5. Seed Applications for Alex Rivera (student 1) across different Kanban statuses
    $alexStudentId = $studentMap['alex@university.edu'];

    // App 1: Stripe - Offered!
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, interview_date, salary_offered, applied_at) VALUES (?, ?, 0, 'Offered', ?, ?, ?, ?)");
    $stmt->execute([
        $jobIds[0], $alexStudentId,
        "Excited to contribute to Stripe's payment gateways with my hands-on backend expertise in Go and distributed database concepts.",
        date('Y-m-d H:i:s', strtotime('-3 days')),
        '₹1,25,000 / month Stipend',
        date('Y-m-d H:i:s', strtotime('-14 days'))
    ]);
    $app1 = $pdo->lastInsertId();

    // App 2: Google - Interviewing
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, interview_date, applied_at) VALUES (?, ?, 0, 'Interviewing', ?, ?, ?)");
    $stmt->execute([
        $jobIds[1], $alexStudentId,
        "Passionate about Cloud infra and Google Cloud's developer ecosystem.",
        date('Y-m-d 14:00:00', strtotime('+1 day')), // Upcoming interview tomorrow!
        date('Y-m-d H:i:s', strtotime('-8 days'))
    ]);
    $app2 = $pdo->lastInsertId();

    // App 3: Microsoft - Screening
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, applied_at) VALUES (?, ?, 0, 'Screening', ?, ?)");
    $stmt->execute([
        $jobIds[2], $alexStudentId,
        "Experienced in systems programming, data structures, and Azure services.",
        date('Y-m-d H:i:s', strtotime('-7 days')) // 7 days ago -> Triggers smart follow-up reminder!
    ]);
    $app3 = $pdo->lastInsertId();

    // App 4: Razorpay - Applied
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, applied_at) VALUES (?, ?, 0, 'Applied', ?, ?)");
    $stmt->execute([
        $jobIds[3], $alexStudentId,
        "Frontend enthusiast with deep knowledge of React component patterns and state management.",
        date('Y-m-d H:i:s', strtotime('-2 days'))
    ]);
    $app4 = $pdo->lastInsertId();

    // App 5: Custom External App (Wishlist) - Atlassian
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, external_company, external_title, status, cover_note, applied_at) VALUES (NULL, ?, 1, 'Atlassian', 'Software Engineer (Jira Platform)', 'Wishlist', 'Preparing resume tailored for distributed collaboration tools.', ?)");
    $stmt->execute([$alexStudentId, date('Y-m-d H:i:s', strtotime('-1 day'))]);
    $app5 = $pdo->lastInsertId();

    // App 6: Seed another applicant for Stripe & Google (Priya Sharma)
    $priyaStudentId = $studentMap['priya@university.edu'];
    $stmt = $pdo->prepare("INSERT INTO applications (job_id, student_id, is_external, status, cover_note, applied_at) VALUES (?, ?, 0, 'Applied', ?, ?)");
    $stmt->execute([$jobIds[4], $priyaStudentId, "Passionate about data modeling and operations analytics at Amazon.", date('Y-m-d H:i:s', strtotime('-3 days'))]);

    // 6. Application History for Timeline View
    $histData = [
        [$app1, NULL, 'Applied', 'Applied online through CareerPulse portal', date('Y-m-d H:i:s', strtotime('-14 days'))],
        [$app1, 'Applied', 'Screening', 'Resume shortlisted by Stripe University Recruiting team', date('Y-m-d H:i:s', strtotime('-10 days'))],
        [$app1, 'Screening', 'Interviewing', 'Cleared Online Technical Coding assessment (Score: 100%)', date('Y-m-d H:i:s', strtotime('-6 days'))],
        [$app1, 'Interviewing', 'Offered', 'Passed Final Hiring Manager & System Architecture loop. Formal offer extended!', date('Y-m-d H:i:s', strtotime('-1 day'))],

        [$app2, NULL, 'Applied', 'Application submitted for Google Associate Cloud Engineer', date('Y-m-d H:i:s', strtotime('-8 days'))],
        [$app2, 'Applied', 'Screening', 'Selected for Google Online Technical Challenge (G-OTC)', date('Y-m-d H:i:s', strtotime('-4 days'))],
        [$app2, 'Screening', 'Interviewing', 'Google Meet Technical Round 1 scheduled for tomorrow', date('Y-m-d H:i:s', strtotime('-1 day'))],

        [$app3, NULL, 'Applied', 'Application submitted for Microsoft SDE I', date('Y-m-d H:i:s', strtotime('-7 days'))],
        [$app3, 'Applied', 'Screening', 'Initial resume screening in progress with University Relations', date('Y-m-d H:i:s', strtotime('-7 days'))]
    ];

    $hStmt = $pdo->prepare("INSERT INTO application_history (application_id, old_status, new_status, notes, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($histData as $h) {
        $hStmt->execute($h);
    }

    // 7. Interview Prep Notes (Alex Rivera)
    $prepNotes = [
        [
            'app_id' => $app1,
            'round' => 'Round 1: DSA & API Design',
            'date' => date('Y-m-d', strtotime('-8 days')),
            'questions' => "1. Design an idempotent payment charge endpoint with token generation.\n2. LRU Cache implementation with O(1) get and put operations.\n3. Handling race conditions during concurrent account balance deductions.",
            'notes' => 'Interviewer was very impressed with edge case handling on double-spend prevention. Kept code modular and well-commented.',
            'result' => 'Passed'
        ],
        [
            'app_id' => $app1,
            'round' => 'Round 2: System Architecture & Values',
            'date' => date('Y-m-d', strtotime('-4 days')),
            'questions' => "1. How to scale webhook deliveries to 50,000 requests/sec with exponential backoff?\n2. Discussing past failure and what lessons were learned.\n3. Kafka vs RabbitMQ message ordering guarantees.",
            'notes' => 'Answered trade-offs clearly. Showcased deep understanding of distributed locks using Redis.',
            'result' => 'Passed'
        ],
        [
            'app_id' => $app2,
            'round' => 'Round 1: Cloud Architecture & Linux',
            'date' => date('Y-m-d', strtotime('+1 day')),
            'questions' => "Expected: Kubernetes Pod lifecycle, iptables, Subnetting, Python socket programming.",
            'notes' => 'Reviewing Linux kernel namespaces and cgroups tonight. Practice quick mock with classmate.',
            'result' => 'Pending'
        ]
    ];

    $nStmt = $pdo->prepare("INSERT INTO interview_notes (application_id, round_name, interview_date, questions_asked, notes, result) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($prepNotes as $pn) {
        $nStmt->execute([$pn['app_id'], $pn['round'], $pn['date'], $pn['questions'], $pn['notes'], $pn['result']]);
    }

    // 8. Notifications
    $notifs = [
        [
            'user_id' => 7, // Alex
            'title' => 'Offer Extended by Stripe',
            'msg' => 'Stripe has extended a formal internship offer for Software Engineer Intern! Check details in your Tracker.',
            'type' => 'success',
            'link' => '#tracker'
        ],
        [
            'user_id' => 7, // Alex
            'title' => 'Upcoming Google Interview Scheduled',
            'msg' => 'Your Technical Round 1 with Google Cloud Team is scheduled for tomorrow at 2:00 PM IST.',
            'type' => 'info',
            'link' => '#calendar'
        ],
        [
            'user_id' => 7, // Alex
            'title' => 'Application Deadline Alert: Stripe',
            'msg' => 'Deadline for Stripe Software Engineer Intern closes in 2 days. Complete any pending steps!',
            'type' => 'warning',
            'link' => '#portal'
        ],
        [
            'user_id' => 1, // Admin
            'title' => 'New Drive Submission Requires Review',
            'msg' => 'Stripe has posted "Product Security Engineer (Fintech Compliance)". Review and approve in Admin moderation.',
            'type' => 'warning',
            'link' => '#moderation'
        ],
        [
            'user_id' => 2, // Stripe Recruiter
            'title' => 'New Application Received',
            'msg' => 'Alex Rivera has applied for Software Engineer Intern (Backend & Payments).',
            'type' => 'info',
            'link' => '#applicants'
        ]
    ];

    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)");
    foreach ($notifs as $nt) {
        $notifStmt->execute([$nt['user_id'], $nt['title'], $nt['msg'], $nt['type'], $nt['link']]);
    }

    // 9. Activity Logs
    $logs = [
        [$adminId, 'SYSTEM_INIT', 'CareerPulse database initialized and production seed loaded.', '127.0.0.1'],
        [2, 'DRIVE_POSTED', 'Stripe posted new drive: Software Engineer Intern.', '127.0.0.1'],
        [3, 'DRIVE_POSTED', 'Google posted new drive: Associate Cloud Engineer.', '127.0.0.1'],
        [$adminId, 'DRIVE_APPROVED', 'Admin approved drive #1 (Stripe) and drive #2 (Google).', '127.0.0.1'],
        [7, 'APPLICATION_SUBMITTED', 'Alex Rivera applied to Stripe Backend Internship.', '127.0.0.1'],
        [2, 'STATUS_UPDATED', 'Stripe moved Alex Rivera to Offered stage.', '127.0.0.1']
    ];

    $logStmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    foreach ($logs as $l) {
        $logStmt->execute([$l[0], $l[1], $l[2], $l[3]]);
    }
}
