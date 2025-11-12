<?php
/**
 * Books Management Module
 * Handles CRUD operations for books
 */
require_once 'includes/config.php';
requireLogin();

$pageTitle = 'Books Management';
$conn = getDBConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        // Add new book
        $title = sanitizeInput($_POST['book_title']);
        $author = sanitizeInput($_POST['author']);
        $isbn = sanitizeInput($_POST['isbn']);
        $category = sanitizeInput($_POST['category']);
        $total_copies = intval($_POST['total_copies']);
        $publication_year = intval($_POST['publication_year']);
        $publisher = sanitizeInput($_POST['publisher']);
        
        $stmt = $conn->prepare("INSERT INTO books (book_title, author, isbn, category, total_copies, available_copies, publication_year, publisher) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiss", $title, $author, $isbn, $category, $total_copies, $total_copies, $publication_year, $publisher);
        
        if ($stmt->execute()) {
            showSuccess("Book added successfully!");
        } else {
            showError("Error adding book: " . $conn->error);
        }
        $stmt->close();
        header("Location: books.php");
        exit();
    }
    elseif ($action == 'edit') {
        // Update book
        $book_id = intval($_POST['book_id']);
        $title = sanitizeInput($_POST['book_title']);
        $author = sanitizeInput($_POST['author']);
        $isbn = sanitizeInput($_POST['isbn']);
        $category = sanitizeInput($_POST['category']);
        $total_copies = intval($_POST['total_copies']);
        $publication_year = intval($_POST['publication_year']);
        $publisher = sanitizeInput($_POST['publisher']);
        
        $stmt = $conn->prepare("UPDATE books SET book_title=?, author=?, isbn=?, category=?, total_copies=?, publication_year=?, publisher=? WHERE book_id=?");
        $stmt->bind_param("ssssissi", $title, $author, $isbn, $category, $total_copies, $publication_year, $publisher, $book_id);
        
        if ($stmt->execute()) {
            showSuccess("Book updated successfully!");
        } else {
            showError("Error updating book: " . $conn->error);
        }
        $stmt->close();
        header("Location: books.php");
        exit();
    }
    elseif ($action == 'delete') {
        // Delete book
        $book_id = intval($_POST['book_id']);
        
        $stmt = $conn->prepare("DELETE FROM books WHERE book_id=?");
        $stmt->bind_param("i", $book_id);
        
        if ($stmt->execute()) {
            showSuccess("Book deleted successfully!");
        } else {
            showError("Error deleting book: " . $conn->error);
        }
        $stmt->close();
        header("Location: books.php");
        exit();
    }
}

// Handle GET actions (edit, delete)
$editBook = null;
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $book_id = intval($_GET['id']);
        $result = $conn->query("SELECT * FROM books WHERE book_id = $book_id");
        if ($result->num_rows > 0) {
            $editBook = $result->fetch_assoc();
        }
    }
}

// Search functionality
$searchQuery = '';
$whereClause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = sanitizeInput($_GET['search']);
    $whereClause = " WHERE book_title LIKE '%$searchQuery%' OR author LIKE '%$searchQuery%' OR isbn LIKE '%$searchQuery%' OR category LIKE '%$searchQuery%'";
}

// Fetch all books
$books = $conn->query("SELECT * FROM books $whereClause ORDER BY book_id DESC");

include 'includes/header.php';
?>

<div class="books-page">
    <div class="page-header">
        <h1><i class="fas fa-book"></i> Books Management</h1>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus"></i> Add New Book
        </button>
    </div>
    
    <!-- Search Bar -->
    <div class="search-bar">
        <form method="GET" action="" class="search-form">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by title, author, ISBN, or category..." 
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($searchQuery)): ?>
                <a href="books.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Books Table -->
    <div class="card">
        <div class="card-body">
            <?php if ($books->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Total Copies</th>
                            <th>Available</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = $books->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $book['book_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($book['book_title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($book['category']); ?></span></td>
                            <td><?php echo $book['total_copies']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo $book['available_copies']; ?>
                                </span>
                            </td>
                            <td><?php echo $book['publication_year']; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-sm btn-info" onclick='editBook(<?php echo json_encode($book); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteBook(<?php echo $book['book_id']; ?>, '<?php echo addslashes($book['book_title']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center text-muted">No books found. Add your first book!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Book Modal -->
<div id="bookModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle"><i class="fas fa-book"></i> Add New Book</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="" id="bookForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="book_id" id="bookId">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="book_title">Book Title *</label>
                    <input type="text" id="book_title" name="book_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author">Author *</label>
                    <input type="text" id="author" name="author" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn">
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <input type="text" id="category" name="category" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="total_copies">Total Copies *</label>
                    <input type="number" id="total_copies" name="total_copies" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label for="publication_year">Publication Year</label>
                    <input type="number" id="publication_year" name="publication_year" min="1800" max="2100">
                </div>
            </div>
            
            <div class="form-group">
                <label for="publisher">Publisher</label>
                <input type="text" id="publisher" name="publisher">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Book
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
            <p>Are you sure you want to delete this book?</p>
            <p><strong id="deleteBookTitle"></strong></p>
        </div>
        <form method="POST" action="" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="book_id" id="deleteBookId">
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
// Show add book modal
function showAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-book"></i> Add New Book';
    document.getElementById('formAction').value = 'add';
    document.getElementById('bookForm').reset();
    document.getElementById('bookModal').style.display = 'block';
}

// Edit book
function editBook(book) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Book';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('bookId').value = book.book_id;
    document.getElementById('book_title').value = book.book_title;
    document.getElementById('author').value = book.author;
    document.getElementById('isbn').value = book.isbn;
    document.getElementById('category').value = book.category;
    document.getElementById('total_copies').value = book.total_copies;
    document.getElementById('publication_year').value = book.publication_year;
    document.getElementById('publisher').value = book.publisher;
    document.getElementById('bookModal').style.display = 'block';
}

// Delete book
function deleteBook(bookId, bookTitle) {
    document.getElementById('deleteBookId').value = bookId;
    document.getElementById('deleteBookTitle').textContent = bookTitle;
    document.getElementById('deleteModal').style.display = 'block';
}

// Close modals
function closeModal() {
    document.getElementById('bookModal').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const bookModal = document.getElementById('bookModal');
    const deleteModal = document.getElementById('deleteModal');
    if (event.target == bookModal) {
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
