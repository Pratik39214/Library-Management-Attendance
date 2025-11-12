<?php
/**
 * Reports Module
 * Displays various reports for books, students, and attendance
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Reports';
$conn = getDBConnection();

// Get report type
$reportType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'overview';

include 'includes/header.php';
?>

<div class="reports-page">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
    </div>
    
    <!-- Report Type Selector -->
    <div class="report-tabs">
        <a href="?type=overview" class="tab <?php echo $reportType == 'overview' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Overview
        </a>
        <a href="?type=books" class="tab <?php echo $reportType == 'books' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Books Report
        </a>
        <a href="?type=students" class="tab <?php echo $reportType == 'students' ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i> Students Report
        </a>
        <a href="?type=attendance" class="tab <?php echo $reportType == 'attendance' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Attendance Report
        </a>
        <a href="?type=overdue" class="tab <?php echo $reportType == 'overdue' ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i> Overdue Books
        </a>
    </div>
    
    <?php if ($reportType == 'overview'): ?>
        <!-- Overview Report -->
        <div class="report-content">
            <h2>System Overview</h2>
            
            <?php
            // Get statistics
            $totalBooks = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
            $totalStudents = $conn->query("SELECT COUNT(*) as count FROM students WHERE status='active'")->fetch_assoc()['count'];
            $issuedBooks = $conn->query("SELECT COUNT(*) as count FROM book_issues WHERE status='issued'")->fetch_assoc()['count'];
            $overdueBooks = $conn->query("SELECT COUNT(*) as count FROM book_issues WHERE status='issued' AND due_date < CURDATE()")->fetch_assoc()['count'];
            
            // Books by category
            $booksByCategory = $conn->query("SELECT category, COUNT(*) as count FROM books GROUP BY category ORDER BY count DESC");
            
            // Students by department
            $studentsByDept = $conn->query("SELECT department, COUNT(*) as count FROM students WHERE status='active' GROUP BY department ORDER BY count DESC");
            
            // Recent activities
            $recentIssues = $conn->query("
                SELECT bi.issue_date, b.book_title, s.student_name, bi.status
                FROM book_issues bi
                JOIN books b ON bi.book_id = b.book_id
                JOIN students s ON bi.student_id = s.student_id
                ORDER BY bi.issue_date DESC
                LIMIT 10
            ");
            ?>
            
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <h3><?php echo $totalBooks; ?></h3>
                    <p>Total Books</p>
                </div>
                <div class="stat-card stat-info">
                    <h3><?php echo $totalStudents; ?></h3>
                    <p>Active Students</p>
                </div>
                <div class="stat-card stat-warning">
                    <h3><?php echo $issuedBooks; ?></h3>
                    <p>Books Issued</p>
                </div>
                <div class="stat-card stat-danger">
                    <h3><?php echo $overdueBooks; ?></h3>
                    <p>Overdue Books</p>
                </div>
            </div>
            
            <div class="report-grid">
                <div class="card">
                    <div class="card-header">
                        <h3>Books by Category</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $booksByCategory->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td><span class="badge badge-primary"><?php echo $row['count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Students by Department</h3>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $studentsByDept->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><span class="badge badge-info"><?php echo $row['count']; ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    <?php elseif ($reportType == 'books'): ?>
        <!-- Books Report -->
        <?php
        $booksReport = $conn->query("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM book_issues WHERE book_id = b.book_id) as total_issues,
                   (SELECT COUNT(*) FROM book_issues WHERE book_id = b.book_id AND status='issued') as current_issues
            FROM books b
            ORDER BY total_issues DESC
        ");
        ?>
        <div class="report-content">
            <h2>Books Report</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Total Copies</th>
                                <th>Available</th>
                                <th>Total Issues</th>
                                <th>Currently Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($book = $booksReport->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($book['book_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($book['category']); ?></span></td>
                                <td><?php echo $book['total_copies']; ?></td>
                                <td><span class="badge badge-success"><?php echo $book['available_copies']; ?></span></td>
                                <td><?php echo $book['total_issues']; ?></td>
                                <td><?php echo $book['current_issues']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php elseif ($reportType == 'students'): ?>
        <!-- Students Report -->
        <?php
        $studentsReport = $conn->query("
            SELECT s.*,
                   (SELECT COUNT(*) FROM book_issues WHERE student_id = s.student_id) as total_books_issued,
                   (SELECT COUNT(*) FROM book_issues WHERE student_id = s.student_id AND status='issued') as current_books,
                   (SELECT COUNT(*) FROM attendance WHERE student_id = s.student_id AND status='present') as present_days,
                   (SELECT COUNT(*) FROM attendance WHERE student_id = s.student_id AND status='absent') as absent_days
            FROM students s
            WHERE s.status = 'active'
            ORDER BY s.student_name
        ");
        ?>
        <div class="report-content">
            <h2>Students Report</h2>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Roll Number</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Total Books Issued</th>
                                <th>Current Books</th>
                                <th>Present Days</th>
                                <th>Absent Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $studentsReport->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['department']); ?></td>
                                <td><?php echo $student['year_of_study']; ?></td>
                                <td><?php echo $student['total_books_issued']; ?></td>
                                <td><span class="badge badge-warning"><?php echo $student['current_books']; ?></span></td>
                                <td><span class="badge badge-success"><?php echo $student['present_days']; ?></span></td>
                                <td><span class="badge badge-danger"><?php echo $student['absent_days']; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php elseif ($reportType == 'attendance'): ?>
        <!-- Attendance Report -->
        <?php
        $startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : date('Y-m-d');
        
        $attendanceReport = $conn->query("
            SELECT s.student_name, s.roll_number, s.department,
                   COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
                   COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
                   COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count,
                   COUNT(*) as total_days
            FROM students s
            LEFT JOIN attendance a ON s.student_id = a.student_id 
                AND a.attendance_date BETWEEN '$startDate' AND '$endDate'
            WHERE s.status = 'active'
            GROUP BY s.student_id, s.student_name, s.roll_number, s.department
            ORDER BY s.student_name
        ");
        ?>
        <div class="report-content">
            <h2>Attendance Report</h2>
            
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="date-range-form">
                        <input type="hidden" name="type" value="attendance">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date:</label>
                                <input type="date" name="start_date" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="form-group">
                                <label>End Date:</label>
                                <input type="date" name="end_date" value="<?php echo $endDate; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Roll Number</th>
                                <th>Department</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Late</th>
                                <th>Total Days</th>
                                <th>Attendance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $attendanceReport->fetch_assoc()): 
                                $percentage = $row['total_days'] > 0 ? 
                                    round(($row['present_count'] / $row['total_days']) * 100, 2) : 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['department']); ?></td>
                                <td><span class="badge badge-success"><?php echo $row['present_count']; ?></span></td>
                                <td><span class="badge badge-danger"><?php echo $row['absent_count']; ?></span></td>
                                <td><span class="badge badge-warning"><?php echo $row['late_count']; ?></span></td>
                                <td><?php echo $row['total_days']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $percentage >= 75 ? 'success' : 'danger'; ?>">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    <?php elseif ($reportType == 'overdue'): ?>
        <!-- Overdue Books Report -->
        <?php
        $overdueReport = $conn->query("
            SELECT bi.issue_id, b.book_title, b.author, s.student_name, s.roll_number, s.phone,
                   bi.issue_date, bi.due_date, 
                   DATEDIFF(CURDATE(), bi.due_date) as days_overdue
            FROM book_issues bi
            JOIN books b ON bi.book_id = b.book_id
            JOIN students s ON bi.student_id = s.student_id
            WHERE bi.status = 'issued' AND bi.due_date < CURDATE()
            ORDER BY days_overdue DESC
        ");
        ?>
        <div class="report-content">
            <h2>Overdue Books Report</h2>
            <div class="card">
                <div class="card-body">
                    <?php if ($overdueReport->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Student Name</th>
                                <th>Roll Number</th>
                                <th>Phone</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Fine Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $overdueReport->fetch_assoc()): 
                                $fine = $row['days_overdue'] * FINE_PER_DAY;
                            ?>
                            <tr class="overdue-row">
                                <td><strong><?php echo htmlspecialchars($row['book_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['author']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo formatDate($row['issue_date']); ?></td>
                                <td><?php echo formatDate($row['due_date']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $row['days_overdue']; ?> days</span></td>
                                <td><strong class="text-danger">₹<?php echo $fine; ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-center text-muted">No overdue books found!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
