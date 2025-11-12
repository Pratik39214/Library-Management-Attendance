# 📚 Library Management & Attendance System

A comprehensive web-based Library Management and Attendance System built with PHP, MySQL, HTML, CSS, and JavaScript. This system provides complete functionality for managing books, students, book issues/returns, and student attendance tracking.

## ✨ Features

### 🔐 Admin Authentication
- Secure login system with password hashing
- Session management
- Admin dashboard with overview statistics

### 📖 Books Management
- Add, edit, and delete books
- Search books by title, author, ISBN, or category
- Track total and available copies
- View book issue history
- Categorize books by genre

### 👨‍🎓 Student Management
- Add, edit, and delete student records
- Search students by name, roll number, email, or department
- Track student status (active/inactive)
- View student borrowing history
- Department-wise student organization

### 📤 Book Issue & Return System
- Issue books to students with due date tracking
- Return books with automatic fine calculation
- Track overdue books
- View currently issued books
- Automatic inventory management

### 📅 Attendance Management
- Mark daily attendance for students (Present/Absent/Late)
- Date-wise attendance tracking
- Bulk attendance marking
- Quick actions (Mark All Present/Absent)
- Attendance summary and statistics

### 📊 Reports & Analytics
- Overview dashboard with key metrics
- Books report with issue statistics
- Students report with borrowing and attendance data
- Attendance reports with date range filtering
- Overdue books report with fine calculations
- Department-wise and category-wise analytics

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Icons:** Font Awesome 6.4.0
- **Server:** Apache (XAMPP/WAMP/LAMP)

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- XAMPP/WAMP/LAMP (includes Apache, PHP, and MySQL)
- Web browser (Chrome, Firefox, Safari, or Edge)
- Text editor (VS Code, Sublime Text, or any IDE)

## 🚀 Installation Guide

### Step 1: Download and Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project Files

1. Copy the `library_system` folder to your XAMPP installation directory:
   ```
   C:\xampp\htdocs\library_system
   ```
   (On Linux/Mac: `/opt/lampp/htdocs/library_system`)

2. Verify the folder structure:
   ```
   library_system/
   ├── css/
   │   └── style.css
   ├── js/
   │   └── main.js
   ├── includes/
   │   ├── config.php
   │   ├── header.php
   │   └── footer.php
   ├── database/
   │   └── library_system.sql
   ├── login.php
   ├── dashboard.php
   ├── books.php
   ├── students.php
   ├── issue_book.php
   ├── attendance.php
   ├── reports.php
   └── logout.php
   ```

### Step 3: Create Database

1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin
   ```

2. Click on "New" in the left sidebar to create a new database

3. Enter database name: `library_system`

4. Click "Create"

5. Select the `library_system` database

6. Click on "Import" tab

7. Click "Choose File" and select:
   ```
   library_system/database/library_system.sql
   ```

8. Click "Go" to import the database

### Step 4: Configure Database Connection

1. Open `includes/config.php` in a text editor

2. Verify/Update the database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Default is empty for XAMPP
   define('DB_NAME', 'library_system');
   ```

3. Save the file

### Step 5: Access the Application

1. Open your web browser

2. Navigate to:
   ```
   http://localhost/library_system/login.php
   ```

3. Login with default credentials:
   - **Username:** admin
   - **Password:** admin123

4. You're ready to use the system! 🎉

## 📱 Usage Guide

### Admin Login
1. Navigate to `http://localhost/library_system/login.php`
2. Enter username and password
3. Click "Login"

### Managing Books
1. Click "Books" in the navigation menu
2. Click "Add New Book" to add a book
3. Fill in book details (title, author, ISBN, category, copies, etc.)
4. Click "Save Book"
5. Use the search bar to find specific books
6. Click edit icon to modify book details
7. Click delete icon to remove a book

### Managing Students
1. Click "Students" in the navigation menu
2. Click "Add New Student" to register a student
3. Fill in student details (name, roll number, email, phone, department, etc.)
4. Click "Save Student"
5. Use the search bar to find specific students
6. Click edit icon to modify student details
7. Click delete icon to remove a student

### Issuing Books
1. Click "Issue/Return" in the navigation menu
2. Click "Issue New Book"
3. Select a book from the dropdown
4. Select a student from the dropdown
5. Set issue date and due date (default: 14 days)
6. Click "Issue Book"

### Returning Books
1. Go to "Issue/Return" page
2. Find the issued book in the table
3. Click "Return" button
4. Verify return date
5. Click "Confirm Return"
6. Fine will be automatically calculated if overdue

### Marking Attendance
1. Click "Attendance" in the navigation menu
2. Select the date (default: today)
3. Mark each student as Present, Absent, or Late
4. Add optional remarks
5. Click "Save Attendance"
6. Use "Mark All Present" or "Mark All Absent" for quick actions

### Viewing Reports
1. Click "Reports" in the navigation menu
2. Select report type from tabs:
   - **Overview:** System statistics and summaries
   - **Books Report:** Detailed book statistics
   - **Students Report:** Student borrowing and attendance data
   - **Attendance Report:** Date-range attendance analysis
   - **Overdue Books:** List of overdue books with fines

## 🗄️ Database Schema

### Tables

#### 1. admin
Stores admin login credentials
- `admin_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `username` (VARCHAR(50), UNIQUE)
- `password` (VARCHAR(255)) - Hashed password
- `full_name` (VARCHAR(100))
- `email` (VARCHAR(100))
- `created_at` (TIMESTAMP)

#### 2. books
Stores book information
- `book_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `book_title` (VARCHAR(200))
- `author` (VARCHAR(100))
- `isbn` (VARCHAR(20), UNIQUE)
- `category` (VARCHAR(50))
- `total_copies` (INT)
- `available_copies` (INT)
- `publication_year` (YEAR)
- `publisher` (VARCHAR(100))
- `added_date` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### 3. students
Stores student information
- `student_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `student_name` (VARCHAR(100))
- `roll_number` (VARCHAR(50), UNIQUE)
- `email` (VARCHAR(100))
- `phone` (VARCHAR(15))
- `department` (VARCHAR(50))
- `year_of_study` (INT)
- `address` (TEXT)
- `registration_date` (TIMESTAMP)
- `status` (ENUM: 'active', 'inactive')

#### 4. book_issues
Tracks book issue and return records
- `issue_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `book_id` (INT, FOREIGN KEY)
- `student_id` (INT, FOREIGN KEY)
- `issue_date` (DATE)
- `due_date` (DATE)
- `return_date` (DATE)
- `status` (ENUM: 'issued', 'returned', 'overdue')
- `fine_amount` (DECIMAL(10,2))
- `issued_by` (INT, FOREIGN KEY)
- `returned_by` (INT, FOREIGN KEY)
- `notes` (TEXT)
- `created_at` (TIMESTAMP)

#### 5. attendance
Tracks student attendance
- `attendance_id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `student_id` (INT, FOREIGN KEY)
- `attendance_date` (DATE)
- `status` (ENUM: 'present', 'absent', 'late')
- `marked_by` (INT, FOREIGN KEY)
- `remarks` (TEXT)
- `marked_at` (TIMESTAMP)
- UNIQUE KEY: (student_id, attendance_date)

## 📝 Example SQL Queries

### Issue a Book
```sql
-- Update available copies
UPDATE books SET available_copies = available_copies - 1 WHERE book_id = 1;

-- Insert issue record
INSERT INTO book_issues (book_id, student_id, issue_date, due_date, issued_by) 
VALUES (1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1);
```

### Return a Book
```sql
-- Update book availability
UPDATE books SET available_copies = available_copies + 1 WHERE book_id = 1;

-- Update issue record
UPDATE book_issues 
SET return_date = CURDATE(), status = 'returned', returned_by = 1 
WHERE issue_id = 1;
```

### Mark Attendance
```sql
-- Insert or update attendance
INSERT INTO attendance (student_id, attendance_date, status, marked_by) 
VALUES (1, CURDATE(), 'present', 1)
ON DUPLICATE KEY UPDATE status = 'present', marked_at = CURRENT_TIMESTAMP;
```

### Get Issued Books with Details
```sql
SELECT bi.issue_id, b.book_title, s.student_name, s.roll_number, 
       bi.issue_date, bi.due_date, bi.status
FROM book_issues bi
JOIN books b ON bi.book_id = b.book_id
JOIN students s ON bi.student_id = s.student_id
WHERE bi.status = 'issued'
ORDER BY bi.issue_date DESC;
```

### Get Attendance Report
```sql
SELECT s.student_name, s.roll_number,
       COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_days,
       COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_days,
       COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_days
FROM students s
LEFT JOIN attendance a ON s.student_id = a.student_id
GROUP BY s.student_id, s.student_name, s.roll_number;
```

## 🔧 Configuration

### Fine Settings
Edit `includes/config.php` to change fine amount:
```php
define('FINE_PER_DAY', 5); // Fine amount per day for overdue books
```

### Issue Duration
Change default book issue duration:
```php
define('ISSUE_DURATION_DAYS', 14); // Default book issue duration in days
```

### Pagination
Adjust items per page:
```php
define('BOOKS_PER_PAGE', 10); // Number of books per page
```

## 🎨 Customization

### Changing Colors
Edit `css/style.css` and modify CSS variables:
```css
:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --success-color: #27ae60;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --info-color: #16a085;
}
```

### Adding New Admin
Run this SQL query in phpMyAdmin:
```sql
INSERT INTO admin (username, password, full_name, email) 
VALUES ('newadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'New Admin', 'newadmin@library.com');
```
Note: The password hash shown is for 'admin123'. Use PHP's `password_hash()` function to generate new hashes.

## 🐛 Troubleshooting

### Cannot connect to database
- Verify MySQL is running in XAMPP Control Panel
- Check database credentials in `includes/config.php`
- Ensure database `library_system` exists

### Page not found (404)
- Verify files are in `htdocs/library_system/` folder
- Check Apache is running in XAMPP Control Panel
- Clear browser cache

### Login not working
- Verify database is imported correctly
- Check if admin table has records
- Ensure session is enabled in PHP

### CSS/JS not loading
- Check file paths in header.php
- Verify CSS and JS files exist in respective folders
- Clear browser cache

### Permission denied errors
- On Linux/Mac, set proper permissions:
  ```bash
  chmod -R 755 /opt/lampp/htdocs/library_system
  ```

## 📄 File Structure Details

```
library_system/
│
├── css/
│   └── style.css              # Main stylesheet with responsive design
│
├── js/
│   └── main.js                # JavaScript for interactivity and validation
│
├── includes/
│   ├── config.php             # Database configuration and helper functions
│   ├── header.php             # Common header with navigation
│   └── footer.php             # Common footer
│
├── database/
│   └── library_system.sql     # Database schema and sample data
│
├── login.php                  # Admin login page
├── logout.php                 # Logout script
├── dashboard.php              # Main dashboard with statistics
├── books.php                  # Books management (CRUD operations)
├── students.php               # Students management (CRUD operations)
├── issue_book.php             # Book issue and return system
├── attendance.php             # Attendance marking system
├── reports.php                # Various reports and analytics
└── README.md                  # This file
```

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- Session-based authentication
- CSRF protection ready (can be enhanced)

## 🌟 Future Enhancements

- Email notifications for overdue books
- SMS integration for attendance alerts
- Barcode scanning for books
- Student portal for self-service
- Advanced analytics and charts
- Export reports to PDF/Excel
- Multi-language support
- Mobile app integration

## 👥 Default Credentials

**Admin Login:**
- Username: `admin`
- Password: `admin123`

**Important:** Change the default password after first login!

## 📞 Support

For issues, questions, or contributions:
- Check the troubleshooting section
- Review the code comments
- Verify database schema
- Check PHP error logs in XAMPP

## 📜 License

This project is open-source and available for educational purposes.

## 🙏 Acknowledgments

- Font Awesome for icons
- PHP community for excellent documentation
- MySQL for robust database management
- XAMPP for easy local development environment

---

**Developed with ❤️ for Library Management**

*Last Updated: November 2025*
