<?php
/**
 * Admin Dashboard
 * Displays overview statistics and quick links
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Dashboard';

// Get database connection
$conn = getDBConnection();

// Fetch statistics
$stats = array();

// Total books
$result = $conn->query("SELECT COUNT(*) as total FROM books");
$stats['total_books'] = $result->fetch_assoc()['total'];

// Available books
$result = $conn->query("SELECT SUM(available_copies) as available FROM books");
$stats['available_books'] = $result->fetch_assoc()['available'];

// Total students
$result = $conn->query("SELECT COUNT(*) as total FROM students WHERE status = 'active'");
$stats['total_students'] = $result->fetch_assoc()['total'];

// Currently issued books
$result = $conn->query("SELECT COUNT(*) as total FROM book_issues WHERE status = 'issued'");
$stats['issued_books'] = $result->fetch_assoc()['total'];

// Overdue books
$result = $conn->query("SELECT COUNT(*) as total FROM book_issues WHERE status = 'issued' AND due_date < CURDATE()");
$stats['overdue_books'] = $result->fetch_assoc()['total'];

// Today's attendance count
$result = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE attendance_date = CURDATE()");
$stats['today_attendance'] = $result->fetch_assoc()['total'];

// Recent book issues
$recentIssues = $conn->query("
    SELECT bi.issue_id, b.book_title, s.student_name, s.roll_number, 
           bi.issue_date, bi.due_date, bi.status
    FROM book_issues bi
    JOIN books b ON bi.book_id = b.book_id
    JOIN students s ON bi.student_id = s.student_id
    ORDER BY bi.issue_date DESC
    LIMIT 5
");

// Overdue books list
$overdueBooks = $conn->query("
    SELECT bi.issue_id, b.book_title, s.student_name, s.roll_number, 
           bi.issue_date, bi.due_date, DATEDIFF(CURDATE(), bi.due_date) as days_overdue
    FROM book_issues bi
    JOIN books b ON bi.book_id = b.book_id
    JOIN students s ON bi.student_id = s.student_id
    WHERE bi.status = 'issued' AND bi.due_date < CURDATE()
    ORDER BY bi.due_date ASC
    LIMIT 5
");

include 'includes/header.php';
?>

<div class="dashboard">
    <div class="page-header">
        <h1><i class="fas fa-home"></i> Dashboard</h1>
        <p>Welcome back, <?php echo $_SESSION['admin_name']; ?>!</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_books']; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        
        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['available_books']; ?></h3>
                <p>Available Books</p>
            </div>
        </div>
        
        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p>Total Students</p>
            </div>
        </div>
        
        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['issued_books']; ?></h3>
                <p>Issued Books</p>
            </div>
        </div>
        
        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['overdue_books']; ?></h3>
                <p>Overdue Books</p>
            </div>
        </div>
        
        <div class="stat-card stat-purple">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['today_attendance']; ?></h3>
                <p>Today's Attendance</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        <div class="action-buttons">
            <a href="books.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
            <a href="students.php?action=add" class="btn btn-info">
                <i class="fas fa-user-plus"></i> Add New Student
            </a>
            <a href="issue_book.php" class="btn btn-success">
                <i class="fas fa-book-open"></i> Issue Book
            </a>
            <a href="attendance.php" class="btn btn-warning">
                <i class="fas fa-calendar-check"></i> Mark Attendance
            </a>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="dashboard-grid">
        <!-- Recent Book Issues -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Book Issues</h2>
                <a href="issue_book.php" class="btn btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recentIssues->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Student</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($issue = $recentIssues->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($issue['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($issue['student_name']); ?><br>
                                <small class="text-muted"><?php echo $issue['roll_number']; ?></small>
                            </td>
                            <td><?php echo formatDate($issue['issue_date']); ?></td>
                            <td><?php echo formatDate($issue['due_date']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $issue['status'] == 'issued' ? 'warning' : 'success'; ?>">
                                    <?php echo ucfirst($issue['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-muted">No recent book issues</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Overdue Books -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-exclamation-circle"></i> Overdue Books</h2>
                <a href="reports.php?type=overdue" class="btn btn-sm">View All</a>
            </div>
            <div class="card-body">
                <?php if ($overdueBooks->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Student</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($overdue = $overdueBooks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($overdue['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($overdue['student_name']); ?><br>
                                <small class="text-muted"><?php echo $overdue['roll_number']; ?></small>
                            </td>
                            <td><?php echo formatDate($overdue['due_date']); ?></td>
                            <td>
                                <span class="badge badge-danger">
                                    <?php echo $overdue['days_overdue']; ?> days
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center text-muted">No overdue books</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
