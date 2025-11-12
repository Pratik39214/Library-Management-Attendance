<?php
/**
 * Book Issue and Return Module
 * Handles book issuing and returning operations
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Issue & Return Books';
$conn = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'issue') {
        // Issue a book
        $book_id = intval($_POST['book_id']);
        $student_id = intval($_POST['student_id']);
        $issue_date = sanitizeInput($_POST['issue_date']);
        $due_date = sanitizeInput($_POST['due_date']);
        $admin_id = $_SESSION['admin_id'];
        
        // Check if book is available
        $result = $conn->query("SELECT available_copies FROM books WHERE book_id = $book_id");
        $book = $result->fetch_assoc();
        
        if ($book['available_copies'] > 0) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert issue record
                $stmt = $conn->prepare("INSERT INTO book_issues (book_id, student_id, issue_date, due_date, issued_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iissi", $book_id, $student_id, $issue_date, $due_date, $admin_id);
                $stmt->execute();
                
                // Update available copies
                $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = $book_id");
                
                $conn->commit();
                showSuccess("Book issued successfully!");
            } catch (Exception $e) {
                $conn->rollback();
                showError("Error issuing book: " . $e->getMessage());
            }
        } else {
            showError("Book is not available for issue!");
        }
        
        header("Location: issue_book.php");
        exit();
    }
    elseif ($action == 'return') {
        // Return a book
        $issue_id = intval($_POST['issue_id']);
        $return_date = sanitizeInput($_POST['return_date']);
        $admin_id = $_SESSION['admin_id'];
        
        // Get issue details
        $result = $conn->query("SELECT book_id, due_date FROM book_issues WHERE issue_id = $issue_id");
        $issue = $result->fetch_assoc();
        
        // Calculate fine if overdue
        $fine = calculateFine($issue['due_date']);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update issue record
            $stmt = $conn->prepare("UPDATE book_issues SET return_date=?, status='returned', fine_amount=?, returned_by=? WHERE issue_id=?");
            $stmt->bind_param("sdii", $return_date, $fine, $admin_id, $issue_id);
            $stmt->execute();
            
            // Update available copies
            $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = " . $issue['book_id']);
            
            $conn->commit();
            
            if ($fine > 0) {
                showSuccess("Book returned successfully! Fine amount: ₹" . $fine);
            } else {
                showSuccess("Book returned successfully!");
            }
        } catch (Exception $e) {
            $conn->rollback();
            showError("Error returning book: " . $e->getMessage());
        }
        
        header("Location: issue_book.php");
        exit();
    }
}

// Fetch currently issued books
$issuedBooks = $conn->query("
    SELECT bi.issue_id, bi.book_id, bi.student_id, b.book_title, b.author, 
           s.student_name, s.roll_number, bi.issue_date, bi.due_date,
           DATEDIFF(CURDATE(), bi.due_date) as days_overdue
    FROM book_issues bi
    JOIN books b ON bi.book_id = b.book_id
    JOIN students s ON bi.student_id = s.student_id
    WHERE bi.status = 'issued'
    ORDER BY bi.issue_date DESC
");

// Fetch available books for dropdown
$availableBooks = $conn->query("SELECT book_id, book_title, author, available_copies FROM books WHERE available_copies > 0 ORDER BY book_title");

// Fetch active students for dropdown
$activeStudents = $conn->query("SELECT student_id, student_name, roll_number FROM students WHERE status = 'active' ORDER BY student_name");

include 'includes/header.php';
?>

<div class="issue-book-page">
    <div class="page-header">
        <h1><i class="fas fa-exchange-alt"></i> Issue & Return Books</h1>
        <button class="btn btn-primary" onclick="showIssueModal()">
            <i class="fas fa-book-open"></i> Issue New Book
        </button>
    </div>
    
    <!-- Currently Issued Books -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Currently Issued Books</h2>
        </div>
        <div class="card-body">
            <?php if ($issuedBooks->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Issue ID</th>
                            <th>Book Details</th>
                            <th>Student Details</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($issue = $issuedBooks->fetch_assoc()): 
                            $fine = calculateFine($issue['due_date']);
                            $isOverdue = $issue['days_overdue'] > 0;
                        ?>
                        <tr class="<?php echo $isOverdue ? 'overdue-row' : ''; ?>">
                            <td><?php echo $issue['issue_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($issue['book_title']); ?></strong><br>
                                <small class="text-muted">by <?php echo htmlspecialchars($issue['author']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($issue['student_name']); ?><br>
                                <small class="text-muted"><?php echo $issue['roll_number']; ?></small>
                            </td>
                            <td><?php echo formatDate($issue['issue_date']); ?></td>
                            <td><?php echo formatDate($issue['due_date']); ?></td>
                            <td>
                                <?php if ($isOverdue): ?>
                                    <span class="badge badge-danger">
                                        Overdue (<?php echo $issue['days_overdue']; ?> days)
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($fine > 0): ?>
                                    <span class="text-danger"><strong>₹<?php echo $fine; ?></strong></span>
                                <?php else: ?>
                                    <span class="text-muted">₹0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" 
                                        onclick="returnBook(<?php echo $issue['issue_id']; ?>, '<?php echo addslashes($issue['book_title']); ?>', '<?php echo addslashes($issue['student_name']); ?>', <?php echo $fine; ?>)">
                                    <i class="fas fa-undo"></i> Return
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No books currently issued</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Issue Book Modal -->
<div id="issueModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-book-open"></i> Issue New Book</h2>
            <span class="close" onclick="closeIssueModal()">&times;</span>
        </div>
        <form method="POST" action="" id="issueForm">
            <input type="hidden" name="action" value="issue">
            
            <div class="form-group">
                <label for="book_id">Select Book *</label>
                <select id="book_id" name="book_id" required>
                    <option value="">-- Select a Book --</option>
                    <?php 
                    $availableBooks->data_seek(0);
                    while ($book = $availableBooks->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $book['book_id']; ?>">
                        <?php echo htmlspecialchars($book['book_title']); ?> - <?php echo htmlspecialchars($book['author']); ?> 
                        (Available: <?php echo $book['available_copies']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="student_id">Select Student *</label>
                <select id="student_id" name="student_id" required>
                    <option value="">-- Select a Student --</option>
                    <?php 
                    $activeStudents->data_seek(0);
                    while ($student = $activeStudents->fetch_assoc()): 
                    ?>
                    <option value="<?php echo $student['student_id']; ?>">
                        <?php echo htmlspecialchars($student['student_name']); ?> (<?php echo $student['roll_number']; ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="issue_date">Issue Date *</label>
                    <input type="date" id="issue_date" name="issue_date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date *</label>
                    <input type="date" id="due_date" name="due_date" 
                           value="<?php echo date('Y-m-d', strtotime('+' . ISSUE_DURATION_DAYS . ' days')); ?>" required>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeIssueModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Issue Book
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Return Book Modal -->
<div id="returnModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2><i class="fas fa-undo"></i> Return Book</h2>
            <span class="close" onclick="closeReturnModal()">&times;</span>
        </div>
        <form method="POST" action="" id="returnForm">
            <input type="hidden" name="action" value="return">
            <input type="hidden" name="issue_id" id="returnIssueId">
            
            <div class="modal-body">
                <p><strong>Book:</strong> <span id="returnBookTitle"></span></p>
                <p><strong>Student:</strong> <span id="returnStudentName"></span></p>
                <p id="fineInfo" style="display: none;">
                    <strong class="text-danger">Fine Amount: ₹<span id="returnFineAmount"></span></strong>
                </p>
                
                <div class="form-group">
                    <label for="return_date">Return Date *</label>
                    <input type="date" id="return_date" name="return_date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeReturnModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Confirm Return
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show issue book modal
function showIssueModal() {
    document.getElementById('issueModal').style.display = 'block';
}

// Return book
function returnBook(issueId, bookTitle, studentName, fine) {
    document.getElementById('returnIssueId').value = issueId;
    document.getElementById('returnBookTitle').textContent = bookTitle;
    document.getElementById('returnStudentName').textContent = studentName;
    document.getElementById('returnFineAmount').textContent = fine;
    
    if (fine > 0) {
        document.getElementById('fineInfo').style.display = 'block';
    } else {
        document.getElementById('fineInfo').style.display = 'none';
    }
    
    document.getElementById('returnModal').style.display = 'block';
}

// Close modals
function closeIssueModal() {
    document.getElementById('issueModal').style.display = 'none';
}

function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const issueModal = document.getElementById('issueModal');
    const returnModal = document.getElementById('returnModal');
    if (event.target == issueModal) {
        closeIssueModal();
    }
    if (event.target == returnModal) {
        closeReturnModal();
    }
}

// Auto-calculate due date
document.getElementById('issue_date').addEventListener('change', function() {
    const issueDate = new Date(this.value);
    const dueDate = new Date(issueDate);
    dueDate.setDate(dueDate.getDate() + <?php echo ISSUE_DURATION_DAYS; ?>);
    document.getElementById('due_date').value = dueDate.toISOString().split('T')[0];
});
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>
