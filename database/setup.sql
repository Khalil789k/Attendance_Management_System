-- Attendance Management System Database Schema
-- Created for professional web application

CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users table for admin and teachers
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'teacher') DEFAULT 'teacher',
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(20) NOT NULL,
    section VARCHAR(10) NOT NULL,
    academic_year VARCHAR(20) DEFAULT '2024-25',
    teacher_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_class_section (class_name, section, academic_year)
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    class_id INT NOT NULL,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    parent_name VARCHAR(100),
    parent_phone VARCHAR(15),
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused') DEFAULT 'present',
    marked_by INT NOT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, date)
);

-- Subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(50) NOT NULL,
    subject_code VARCHAR(20) UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Class Subjects mapping
CREATE TABLE class_subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_class_subject (class_id, subject_id)
);

-- Insert default admin user (Pakistan context)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Muhammad Ali', 'admin@university.edu.pk', 'admin');

-- Insert sample classes (Pakistan education system)
INSERT INTO classes (class_name, section, academic_year) VALUES 
('BS Computer Science', 'A', '2024-25'),
('BS Computer Science', 'B', '2024-25'),
('BS Software Engineering', 'A', '2024-25'),
('BS Information Technology', 'A', '2024-25');

-- Insert sample subjects (Computer Science focused for Pakistan)
INSERT INTO subjects (subject_name, subject_code) VALUES 
('Computer Science', 'CS101'),
('Programming Fundamentals', 'PF101'),
('Web Development', 'WD101'),
('Database Management', 'DB101'),
('Data Structures', 'DS101'),
('Object Oriented Programming', 'OOP101'),
('Software Engineering', 'SE101'),
('Computer Networks', 'CN101');

-- Insert sample students (Pakistani names, male only)
INSERT INTO students (roll_number, full_name, email, phone, class_id, gender, parent_name, parent_phone) VALUES 
('10A001', 'Ahmed Ali Khan', 'ahmed@email.com', '03001234567', 1, 'male', 'Ali Khan', '03001234568'),
('10A002', 'Muhammad Hassan', 'hassan@email.com', '03001234569', 1, 'male', 'Abdul Hassan', '03001234570'),
('10A003', 'Usman Ahmed', 'usman@email.com', '03001234571', 1, 'male', 'Ahmed Usman', '03001234572'),
('10B001', 'Bilal Khan', 'bilal@email.com', '03001234573', 2, 'male', 'Khan Bilal', '03001234574'),
('10B002', 'Hamza Ali', 'hamza@email.com', '03001234575', 2, 'male', 'Ali Hamza', '03001234576'),
('9A001', 'Zain Ahmed', 'zain@email.com', '03001234577', 3, 'male', 'Ahmed Zain', '03001234578'),
('9A002', 'Omar Hassan', 'omar@email.com', '03001234579', 3, 'male', 'Hassan Omar', '03001234580'),
('9B001', 'Yusuf Khan', 'yusuf@email.com', '03001234581', 4, 'male', 'Khan Yusuf', '03001234582'); 