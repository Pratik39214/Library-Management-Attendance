<?php
/**
 * Attendance Management Module
 * Handles student attendance marking and viewing
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Attendance Management';
$conn = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'mark_attendance') {
        // Mark attendance for multiple students
        $attendance_date = sanitizeInput($_POST['attendance_date']);
        $admin_id = $_SESSION['admin_id'];
        $success_count = 0;
        $error_count = 0;
        
        // Get all active students
        $students = $conn->query("SELECT student_id FROM students WHERE status = 'active'");
        
        while ($student = $students->fetch_assoc()) {
            $student_id = $student['student_id'];
            $status = isset($_POST['status_' . $student_id]) ? sanitizeInput($_POST['status_' . $student_id]) : 'absent';
            $remarks = isset($_POST['remarks_' . $student_id]) ? sanitizeInput($_POST['remarks_' . $student_id]) : '';
            
            // Insert or update attendance
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, attendance_date, status, marked_by, remarks) 
                                   VALUES (?, ?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE status=?, marked_by=?, remarks=?, marked_at=CURRENT_TIMESTAMP");
            $stmt->bind_param("issisis", $student_id, $attendance_date, $status, $admin_id, $remarks, $status, $admin_id, $remarks);
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
            $stmt->close();
        }
        
        if ($success_count > 0) {
            showSuccess("Attendance marked successfully for $success_count students!");
        }
        if ($error_count > 0) {
            showError("Failed to mark attendance for $error_count students!");
        }
        
        header("Location: attendance.php?date=" . $attendance_date);
        exit();
    }
}

// Get selected date (default to today)
$selected_date = isset($_GET['date']) ? sanitizeInput($_GET['date']) : date('Y-m-d');

// Fetch students with their attendance for selected date
$studentsWithAttendance = $conn->query("
    SELECT s.student_id, s.student_name, s.roll_number, s.department, s.year_of_study,
           a.status, a.remarks
    FROM students s
    LEFT JOIN attendance a ON s.student_id = a.student_id AND a.attendance_date = '$selected_date'
    WHERE s.status = 'active'
    ORDER BY s.student_name
");

include 'includes/header.php';
?>

<div class="attendance-page">
    <div class="page-header">
        <h1><i class="fas fa-calendar-check"></i> Attendance Management</h1>
        <a href="reports.php?type=attendance" class="btn btn-info">
            <i class="fas fa-chart-bar"></i> View Reports
        </a>
    </div>
    
    <!-- Date Selection -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="" class="date-selector">
                <div class="form-group-inline">
                    <label for="date"><i class="fas fa-calendar"></i> Select Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo $selected_date; ?>" 
                           onchange="this.form.submit()">
                    <span class="date-info">
                        <?php 
                        $dateObj = new DateTime($selected_date);
                        echo $dateObj->format('l, F j, Y'); 
                        ?>
                    </span>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Attendance Form -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-users"></i> Mark Attendance</h2>
            <div class="quick-actions-inline">
                <button type="button" class="btn btn-sm btn-success" onclick="markAllPresent()">
                    <i class="fas fa-check-double"></i> Mark All Present
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="markAllAbsent()">
                    <i class="fas fa-times"></i> Mark All Absent
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($studentsWithAttendance->num_rows > 0): ?>
            <form method="POST" action="" id="attendanceForm">
                <input type="hidden" name="action" value="mark_attendance">
                <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
                
                <div class="table-responsive">
                    <table class="table attendance-table">
                        <thead>
                            <tr>
                                <th>Roll No.</th>
                                <th>Student Name</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $studentsWithAttendance->fetch_assoc()): 
                                $currentStatus = $student['status'] ?? 'present';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><strong><?php echo htmlspecialchars($student['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['department']); ?></td>
                                <td><?php echo $student['year_of_study']; ?></td>
                                <td>
                                    <div class="radio-group">
                                        <label class="radio-label">
                                            <input type="radio" name="status_<?php echo $student['student_id']; ?>" 
                                                   value="present" <?php echo $currentStatus == 'present' ? 'checked' : ''; ?>>
                                            <span class="badge badge-success">Present</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="status_<?php echo $student['student_id']; ?>" 
                                                   value="absent" <?php echo $currentStatus == 'absent' ? 'checked' : ''; ?>>
                                            <span class="badge badge-danger">Absent</span>
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="status_<?php echo $student['student_id']; ?>" 
                                                   value="late" <?php echo $currentStatus == 'late' ? 'checked' : ''; ?>>
                                            <span class="badge badge-warning">Late</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="remarks_<?php echo $student['student_id']; ?>" 
                                           class="form-control-sm" placeholder="Optional remarks"
                                           value="<?php echo htmlspecialchars($student['remarks'] ?? ''); ?>">
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Attendance
                    </button>
                </div>
            </form>
            <?php else: ?>
            <p class="text-center text-muted">No active students found</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Attendance Summary -->
    <?php
    $summary = $conn->query("
        SELECT 
            COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
            COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count,
            COUNT(CASE WHEN status = 'late' THEN 1 END) as late_count,
            COUNT(*) as total_marked
        FROM attendance
        WHERE attendance_date = '$selected_date'
    ")->fetch_assoc();
    
    if ($summary['total_marked'] > 0):
    ?>
    <div class="attendance-summary">
        <h3><i class="fas fa-chart-pie"></i> Today's Summary</h3>
        <div class="summary-cards">
            <div class="summary-card summary-present">
                <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                <div class="summary-content">
                    <h4><?php echo $summary['present_count']; ?></h4>
                    <p>Present</p>
                </div>
            </div>
            <div class="summary-card summary-absent">
                <div class="summary-icon"><i class="fas fa-times-circle"></i></div>
                <div class="summary-content">
                    <h4><?php echo $summary['absent_count']; ?></h4>
                    <p>Absent</p>
                </div>
            </div>
            <div class="summary-card summary-late">
                <div class="summary-icon"><i class="fas fa-clock"></i></div>
                <div class="summary-content">
                    <h4><?php echo $summary['late_count']; ?></h4>
                    <p>Late</p>
                </div>
            </div>
            <div class="summary-card summary-total">
                <div class="summary-icon"><i class="fas fa-users"></i></div>
                <div class="summary-content">
                    <h4><?php echo $summary['total_marked']; ?></h4>
                    <p>Total Marked</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Mark all students as present
function markAllPresent() {
    const radios = document.querySelectorAll('input[type="radio"][value="present"]');
    radios.forEach(radio => radio.checked = true);
}

// Mark all students as absent
function markAllAbsent() {
    const radios = document.querySelectorAll('input[type="radio"][value="absent"]');
    radios.forEach(radio => radio.checked = true);
}

// Confirm before leaving if form is modified
let formModified = false;
document.getElementById('attendanceForm').addEventListener('change', function() {
    formModified = true;
});

window.addEventListener('beforeunload', function(e) {
    if (formModified) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.getElementById('attendanceForm').addEventListener('submit', function() {
    formModified = false;
});
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
