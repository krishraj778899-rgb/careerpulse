-- CareerPulse Database Schema
-- Optimized for MySQL 8+ on WAMP Server

CREATE DATABASE IF NOT EXISTS `careerpulse_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `careerpulse_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'company', 'admin') NOT NULL,
    `status` ENUM('active', 'pending', 'suspended') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Student Profiles
CREATE TABLE IF NOT EXISTS `student_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `phone` VARCHAR(30) NULL,
    `department` VARCHAR(100) DEFAULT 'Computer Science',
    `cgpa` DECIMAL(3,2) DEFAULT 8.00,
    `graduation_year` INT DEFAULT 2026,
    `skills` TEXT NULL,
    `resume_filename` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `github_url` VARCHAR(255) NULL,
    `linkedin_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Company Profiles
CREATE TABLE IF NOT EXISTS `company_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `company_name` VARCHAR(150) NOT NULL,
    `website` VARCHAR(255) NULL,
    `industry` VARCHAR(100) DEFAULT 'Technology',
    `location` VARCHAR(150) DEFAULT 'Bengaluru, India',
    `logo_url` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `is_verified` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_company_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Jobs / Drives
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'Software Engineering',
    `type` ENUM('Full-time', 'Internship', 'Contract', 'Part-time') DEFAULT 'Full-time',
    `workplace_type` ENUM('Remote', 'On-site', 'Hybrid') DEFAULT 'Hybrid',
    `location` VARCHAR(150) DEFAULT 'Bengaluru, India',
    `salary_range` VARCHAR(100) DEFAULT '₹8,00,000 - ₹14,00,000 / yr',
    `min_cgpa` DECIMAL(3,2) DEFAULT 7.00,
    `eligible_branches` VARCHAR(255) DEFAULT 'Computer Science, Information Technology, Electronics',
    `eligible_years` VARCHAR(100) DEFAULT '2025, 2026, 2027',
    `deadline` DATE NOT NULL,
    `description` TEXT NOT NULL,
    `requirements` TEXT NOT NULL,
    `skills_required` TEXT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'closed') DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_job_company` FOREIGN KEY (`company_id`) REFERENCES `company_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Applications (both internal jobs and student custom external tracker items)
CREATE TABLE IF NOT EXISTS `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT NULL,
    `student_id` INT NOT NULL,
    `is_external` TINYINT(1) DEFAULT 0,
    `external_company` VARCHAR(150) NULL,
    `external_title` VARCHAR(150) NULL,
    `status` ENUM('Wishlist', 'Applied', 'Screening', 'Interviewing', 'Offered', 'Rejected') DEFAULT 'Applied',
    `cover_note` TEXT NULL,
    `resume_attached` VARCHAR(255) NULL,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `interview_date` DATETIME NULL,
    `salary_offered` VARCHAR(100) NULL,
    CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Application Status History (Timeline)
CREATE TABLE IF NOT EXISTS `application_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `old_status` VARCHAR(50) NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `notes` TEXT NULL,
    `changed_by_user_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_history_app` FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Interview Prep Notes
CREATE TABLE IF NOT EXISTS `interview_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_id` INT NOT NULL,
    `round_name` VARCHAR(100) NOT NULL,
    `interview_date` DATE NULL,
    `questions_asked` TEXT NULL,
    `notes` TEXT NULL,
    `result` ENUM('Passed', 'Pending', 'Failed') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notes_app` FOREIGN KEY (`application_id`) REFERENCES `applications`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    `link` VARCHAR(255) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Activity Audit Logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
