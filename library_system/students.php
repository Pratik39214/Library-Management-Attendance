<?php
/**
 * Students Management Module
 * Handles CRUD operations for students
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Students Management';
$conn = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        // Add new student
        $name = sanitizeInput($_POST['student_name']);
        $roll_number = sanitizeInput($_POST['roll_number']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $department = sanitizeInput($_POST['department']);
        $year_of_study = intval($_POST['year_of_study']);
        $address = sanitizeInput($_POST['address']);
        
        $stmt = $conn->prepare("INSERT INTO students (student_name, roll_number, email, phone, department, year_of_study, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $name, $roll_number, $email, $phone, $department, $year_of_study, $address);
        
        if ($stmt->execute()) {
            showSuccess("Student added successfully!");
        } else {
            showError("Error adding student: " . $conn->error);
        }
        $stmt->close();
        header("Location: students.php");
        exit();
    }
    elseif ($action == 'edit') {
        // Update student
        $student_id = intval($_POST['student_id']);
        $name = sanitizeInput($_POST['student_name']);
        $roll_number = sanitizeInput($_POST['roll_number']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $department = sanitizeInput($_POST['department']);
        $year_of_study = intval($_POST['year_of_study']);
        $address = sanitizeInput($_POST['address']);
        $status = sanitizeInput($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE students SET student_name=?, roll_number=?, email=?, phone=?, department=?, year_of_study=?, address=?, status=? WHERE student_id=?");
        $stmt->bind_param("sssssissi", $name, $roll_number, $email, $phone, $department, $year_of_study, $address, $status, $student_id);
        
        if ($stmt->execute()) {
            showSuccess("Student updated successfully!");
        } else {
            showError("Error updating student: " . $conn->error);
        }
        $stmt->close();
        header("Location: students.php");
        exit();
    }
    elseif ($action == 'delete') {
        // Delete student
        $student_id = intval($_POST['student_id']);
        
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id=?");
        $stmt->bind_param("i", $student_id);
        
        if ($stmt->execute()) {
            showSuccess("Student deleted successfully!");
        } else {
            showError("Error deleting student: " . $conn->error);
        }
        $stmt->close();
        header("Location: students.php");
        exit();
    }
}

// Search functionality
$searchQuery = '';
$whereClause = "WHERE status = 'active'";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = sanitizeInput($_GET['search']);
    $whereClause = " WHERE (student_name LIKE '%$searchQuery%' OR roll_number LIKE '%$searchQuery%' OR email LIKE '%$searchQuery%' OR department LIKE '%$searchQuery%')";
}

// Fetch all students
$students = $conn->query("SELECT * FROM students $whereClause ORDER BY student_id DESC");

include 'includes/header.php';
?>

<div class="students-page">
    <div class="page-header">
        <h1><i class="fas fa-user-graduate"></i> Students Management</h1>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-user-plus"></i> Add New Student
        </button>
    </div>
    
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="" class="search-form">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by name, roll number, email, or department..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($searchQuery)): ?>
                <a href="students.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Students Table -->
    <div class="card">
        <div class="card-body">
            <?php if ($students->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Roll Number</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Year</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($student['student_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($student['department']); ?></span></td>
                            <td><?php echo $student['year_of_study']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $student['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($student['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick='editStudent(<?php echo json_encode($student); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['student_id']; ?>, '<?php echo addslashes($student['student_name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No students found. Add your first student!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Student Modal -->
<div id="studentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-user-graduate"></i> Add New Student</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="" id="studentForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="student_id" id="studentId">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="student_name">Student Name *</label>
                    <input type="text" id="student_name" name="student_name" required>
                </div>
                
                <div class="form-group">
                    <label for="roll_number">Roll Number *</label>
                    <input type="text" id="roll_number" name="roll_number" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department *</label>
                    <input type="text" id="department" name="department" required>
                </div>
                
                <div class="form-group">
                    <label for="year_of_study">Year of Study *</label>
                    <select id="year_of_study" name="year_of_study" required>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group" id="statusGroup" style="display: none;">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Student
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
            <span class="close" onclick="closeDeleteModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this student?</p>
            <p><strong id="deleteStudentName"></strong></p>
        </div>
        <form method="POST" action="" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="student_id" id="deleteStudentId">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show add student modal
function showAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-graduate"></i> Add New Student';
    document.getElementById('formAction').value = 'add';
    document.getElementById('studentForm').reset();
    document.getElementById('statusGroup').style.display = 'none';
    document.getElementById('studentModal').style.display = 'block';
}

// Edit student
function editStudent(student) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Student';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('studentId').value = student.student_id;
    document.getElementById('student_name').value = student.student_name;
    document.getElementById('roll_number').value = student.roll_number;
    document.getElementById('email').value = student.email;
    document.getElementById('phone').value = student.phone;
    document.getElementById('department').value = student.department;
    document.getElementById('year_of_study').value = student.year_of_study;
    document.getElementById('address').value = student.address;
    document.getElementById('status').value = student.status;
    document.getElementById('statusGroup').style.display = 'block';
    document.getElementById('studentModal').style.display = 'block';
}

// Delete student
function deleteStudent(studentId, studentName) {
    document.getElementById('deleteStudentId').value = studentId;
    document.getElementById('deleteStudentName').textContent = studentName;
    document.getElementById('deleteModal').style.display = 'block';
}

// Close modals
function closeModal() {
    document.getElementById('studentModal').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const studentModal = document.getElementById('studentModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target == studentModal) {
        closeModal();
    }
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
}
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
