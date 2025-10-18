-- Advanced Event Manager Database Schema
USE event_manager;

-- Users table with college integration
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'faculty', 'student') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    college_id VARCHAR(20),
    naac_grade ENUM('A++', 'A+', 'A', 'B++', 'B+', 'B', 'C'),
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_college (college_id),
    INDEX idx_role (role)
);

-- Advanced Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    max_participants INT DEFAULT 0,
    current_participants INT DEFAULT 0,
    category ENUM('academic', 'cultural', 'sports', 'technical', 'workshop') DEFAULT 'academic',
    status ENUM('upcoming', 'ongoing', 'completed', 'cancelled') DEFAULT 'upcoming',
    event_image VARCHAR(255),
    registration_deadline DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_date (event_date),
    INDEX idx_category (category),
    INDEX idx_status (status)
);

-- Event registrations table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    UNIQUE KEY unique_registration (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    marked_by INT NOT NULL,
    attendance_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_by INT NOT NULL,
    target_audience ENUM('all', 'members', 'admins') DEFAULT 'all',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Colleges table for NAAC integration
CREATE TABLE colleges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    college_id VARCHAR(20) UNIQUE NOT NULL,
    college_name VARCHAR(200) NOT NULL,
    naac_grade ENUM('A++', 'A+', 'A', 'B++', 'B+', 'B', 'C') NOT NULL,
    location VARCHAR(100),
    established_year YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample colleges
INSERT INTO colleges (college_id, college_name, naac_grade, location, established_year) VALUES 
('COL001', 'Government Engineering College', 'A+', 'Mumbai', 1985),
('COL002', 'St. Xavier College', 'A++', 'Mumbai', 1869),
('COL003', 'Mithibai College', 'A', 'Mumbai', 1961);

-- Insert default users
INSERT INTO users (username, email, password, full_name, role, college_id, naac_grade) VALUES 
('admin', 'admin@eventmanager.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'COL001', 'A+'),
('faculty1', 'faculty@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'faculty', 'COL001', 'A+'),
('student1', 'student@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Johnson', 'student', 'COL001', 'A+');

-- Insert sample events
INSERT INTO events (title, description, event_date, event_time, venue, max_participants, category, created_by, registration_deadline) VALUES 
('Annual Tech Symposium 2024', 'Premier technology conference featuring industry experts', '2024-04-15', '09:00:00', 'Main Auditorium', 200, 'technical', 1, '2024-04-10 23:59:59'),
('Cultural Fest - Kaleidoscope', 'Three-day cultural extravaganza', '2024-03-25', '10:00:00', 'Open Ground', 500, 'cultural', 2, '2024-03-20 23:59:59'),
('Web Development Workshop', 'Hands-on workshop covering modern web technologies', '2024-03-30', '14:00:00', 'Computer Lab A', 40, 'workshop', 2, '2024-03-28 23:59:59');

-- Insert sample registrations
INSERT INTO registrations (user_id, event_id) VALUES (3, 1), (3, 2);