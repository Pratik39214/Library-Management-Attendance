-- ============================================
-- Library Management & Attendance System
-- Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS library_system;
USE library_system;

-- ============================================
-- Table: admin
-- Purpose: Store admin login credentials
-- ============================================
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin (username, password, full_name, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@library.com');

-- ============================================
-- Table: books
-- Purpose: Store book information
-- ============================================
CREATE TABLE IF NOT EXISTS books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    book_title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    category VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    publication_year YEAR,
    publisher VARCHAR(100),
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample books data
INSERT INTO books (book_title, author, isbn, category, total_copies, available_copies, publication_year, publisher) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Fiction', 5, 5, 1925, 'Scribner'),
('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 'Fiction', 3, 3, 1960, 'Harper Perennial'),
('1984', 'George Orwell', '978-0451524935', 'Fiction', 4, 4, 1949, 'Signet Classic'),
('Introduction to Algorithms', 'Thomas H. Cormen', '978-0262033848', 'Computer Science', 2, 2, 2009, 'MIT Press'),
('Clean Code', 'Robert C. Martin', '978-0132350884', 'Computer Science', 3, 3, 2008, 'Prentice Hall');

-- ============================================
-- Table: students
-- Purpose: Store student information
-- ============================================
CREATE TABLE IF NOT EXISTS students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    department VARCHAR(50),
    year_of_study INT,
    address TEXT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Sample students data
INSERT INTO students (student_name, roll_number, email, phone, department, year_of_study, address) VALUES
('John Doe', 'STU001', 'john.doe@student.com', '1234567890', 'Computer Science', 2, '123 Main St'),
('Jane Smith', 'STU002', 'jane.smith@student.com', '0987654321', 'Electronics', 3, '456 Oak Ave'),
('Mike Johnson', 'STU003', 'mike.j@student.com', '5551234567', 'Mechanical', 1, '789 Pine Rd'),
('Sarah Williams', 'STU004', 'sarah.w@student.com', '5559876543', 'Computer Science', 2, '321 Elm St'),
('David Brown', 'STU005', 'david.b@student.com', '5556789012', 'Civil', 4, '654 Maple Dr');

-- ============================================
-- Table: book_issues
-- Purpose: Track book issue and return records
-- ============================================
CREATE TABLE IF NOT EXISTS book_issues (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('issued', 'returned', 'overdue') DEFAULT 'issued',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    issued_by INT,
    returned_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES admin(admin_id),
    FOREIGN KEY (returned_by) REFERENCES admin(admin_id)
);

-- Sample book issues
INSERT INTO book_issues (book_id, student_id, issue_date, due_date, status, issued_by) VALUES
(1, 1, '2025-11-01', '2025-11-15', 'issued', 1),
(2, 2, '2025-11-05', '2025-11-19', 'issued', 1);

-- ============================================
-- Table: attendance
-- Purpose: Track student attendance
-- ============================================
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    marked_by INT,
    remarks TEXT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES admin(admin_id),
    UNIQUE KEY unique_attendance (student_id, attendance_date)
);

-- Sample attendance records
INSERT INTO attendance (student_id, attendance_date, status, marked_by) VALUES
(1, '2025-11-11', 'present', 1),
(2, '2025-11-11', 'present', 1),
(3, '2025-11-11', 'absent', 1),
(4, '2025-11-11', 'present', 1),
(5, '2025-11-11', 'late', 1);

-- ============================================
-- Example Queries
-- ============================================

-- Query 1: Issue a book to a student
-- UPDATE books SET available_copies = available_copies - 1 WHERE book_id = 1;
-- INSERT INTO book_issues (book_id, student_id, issue_date, due_date, issued_by) 
-- VALUES (1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1);

-- Query 2: Return a book
-- UPDATE books SET available_copies = available_copies + 1 WHERE book_id = 1;
-- UPDATE book_issues SET return_date = CURDATE(), status = 'returned', returned_by = 1 
-- WHERE issue_id = 1;

-- Query 3: Mark attendance for a student
-- INSERT INTO attendance (student_id, attendance_date, status, marked_by) 
-- VALUES (1, CURDATE(), 'present', 1)
-- ON DUPLICATE KEY UPDATE status = 'present', marked_at = CURRENT_TIMESTAMP;

-- Query 4: Get all issued books with student and book details
-- SELECT bi.issue_id, b.book_title, s.student_name, s.roll_number, 
--        bi.issue_date, bi.due_date, bi.status
-- FROM book_issues bi
-- JOIN books b ON bi.book_id = b.book_id
-- JOIN students s ON bi.student_id = s.student_id
-- WHERE bi.status = 'issued'
-- ORDER BY bi.issue_date DESC;

-- Query 5: Get attendance report for a specific date
-- SELECT s.student_name, s.roll_number, s.department, a.status, a.remarks
-- FROM attendance a
-- JOIN students s ON a.student_id = s.student_id
-- WHERE a.attendance_date = '2025-11-11'
-- ORDER BY s.student_name;

-- Query 6: Get student attendance summary
-- SELECT s.student_name, s.roll_number,
--        COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
--        COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
--        COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days
-- FROM students s
-- LEFT JOIN attendance a ON s.student_id = a.student_id
-- GROUP BY s.student_id, s.student_name, s.roll_number;

-- ============================================
-- Views for easier data access
-- ============================================

-- View: Current issued books
CREATE OR REPLACE VIEW v_current_issues AS
SELECT 
    bi.issue_id,
    b.book_title,
    b.author,
    s.student_name,
    s.roll_number,
    bi.issue_date,
    bi.due_date,
    DATEDIFF(CURDATE(), bi.due_date) as days_overdue,
    bi.status
FROM book_issues bi
JOIN books b ON bi.book_id = b.book_id
JOIN students s ON bi.student_id = s.student_id
WHERE bi.status = 'issued';

-- View: Today's attendance
CREATE OR REPLACE VIEW v_today_attendance AS
SELECT 
    s.student_id,
    s.student_name,
    s.roll_number,
    s.department,
    COALESCE(a.status, 'not_marked') as status
FROM students s
LEFT JOIN attendance a ON s.student_id = a.student_id AND a.attendance_date = CURDATE()
WHERE s.status = 'active';
