<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/setup.php';

$pdo = new PDO("mysql:host=127.0.0.1;port=3306;charset=utf8mb4", "root", "");
$res = runDatabaseSetup($pdo, true); // force reset to ensure clean seed
echo json_encode($res) . "\n";

$db = getDB();
echo "Users: " . $db->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
echo "Companies: " . $db->query("SELECT COUNT(*) FROM company_profiles")->fetchColumn() . "\n";
echo "Students: " . $db->query("SELECT COUNT(*) FROM student_profiles")->fetchColumn() . "\n";
echo "Jobs: " . $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn() . "\n";
echo "Applications: " . $db->query("SELECT COUNT(*) FROM applications")->fetchColumn() . "\n";
echo "Notifications: " . $db->query("SELECT COUNT(*) FROM notifications")->fetchColumn() . "\n";
