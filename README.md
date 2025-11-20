# Library Management & Attendance System 

> A simple PHP + MySQL project suitable for BCA college submission. Includes library (books, issue/return) and attendance (students, mark attendance, reports).

---

## Project structure

```
library-attendance-system/
├── README.md
├── sql/
│   └── schema.sql
├── config/
│   └── db.php
├── public/
│   ├── index.php            # Dashboard / login redirect
│   ├── login.php
│   ├── logout.php
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/app.js
│   ├── books.php           # View books
│   ├── add_book.php
│   ├── edit_book.php
│   ├── delete_book.php
│   ├── issue_book.php
│   ├── return_book.php
│   ├── students.php        # View students
│   ├── add_student.php
│   ├── attendance.php      # Mark attendance
│   ├── view_attendance.php
│   └── reports.php
└── vendor/                 # (optional) third-party libs
```

---

## README.md

```markdown
# Library Management & Attendance System

## Tech stack
- PHP (>=7.4)
- MySQL
- HTML/CSS/JS

## Setup
1. Create a database (e.g., `library_attendance`).
2. Import `sql/schema.sql` into MySQL.
3. Update `config/db.php` with your DB credentials.
4. Put the project folder in your web server (e.g., `htdocs` or `www`).
5. Open `public/index.php` in browser. Default admin login: `admin@example.com` / `admin123`.

## Notes
- This is a simple implementation suitable for college submission. You can expand features (roles, better auth, file uploads).
```

---

## sql/schema.sql

```sql
-- Database: library_attendance

CREATE DATABASE IF NOT EXISTS library_attendance;
USE library_attendance;

-- Admin / Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff','student') DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students (for attendance and library membership)
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  roll VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150),
  phone VARCHAR(20),
  class VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(200),
  isbn VARCHAR(50),
  copies INT DEFAULT 1,
  available INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Book issues
CREATE TABLE issues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  student_id INT NOT NULL,
  issue_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE DEFAULT NULL,
  status ENUM('issued','returned','overdue') DEFAULT 'issued',
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Attendance: one row per student per date
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  `date` DATE NOT NULL,
  status ENUM('present','absent') NOT NULL,
  marked_by INT, -- user who marked
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY student_date (student_id, `date`),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@example.com', '" + "' + md5('admin123') + "'" + "', 'admin');
```

> **Note:** The schema inserts an admin using an MD5-hashed password for simplicity. In production use password_hash() with `PASSWORD_DEFAULT`.

---

## config/db.php

```php
<?php
// config/db.php
$host = '127.0.0.1';
$db   = 'library_attendance';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
```

---

## public/login.php

```php
<?php
// public/login.php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // For the demo we accept md5 stored in schema; support password_verify if using password_hash.
        if ($user['password'] === md5($password) || password_verify($password, $user['password'])) {
            $_SESSION['user'] = [ 'id' => $user['id'], 'name' => $user['name'], 'role' => $user['role'] ];
            header('Location: index.php');
            exit;
        }
    }
    $error = 'Invalid credentials.';
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Library & Attendance</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <?php if (!empty($error)): ?><p class="error"><?=htmlspecialchars($error)?></p><?php endif; ?>
    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
```

---

## public/index.php (Dashboard)

```php
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="nav">
    <a href="books.php">Books</a>
    <a href="students.php">Students</a>
    <a href="attendance.php">Attendance</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php">Logout</a>
  </div>
  <div class="container">
    <h1>Welcome, <?=htmlspecialchars($user['name'])?></h1>
    <p>Role: <?=htmlspecialchars($user['role'])?></p>
  </div>
</body>
</html>
```

---

## public/books.php (list + basic actions)

```php
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

// Fetch books
$stmt = $pdo->query('SELECT * FROM books ORDER BY created_at DESC');
$books = $stmt->fetchAll();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Books</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <div class="container">
    <h2>Books <a class="btn" href="add_book.php">Add Book</a></h2>
    <table>
      <tr><th>ID</th><th>Title</th><th>Author</th><th>ISBN</th><th>Copies</th><th>Available</th><th>Actions</th></tr>
      <?php foreach($books as $b): ?>
      <tr>
        <td><?=$b['id']?></td>
        <td><?=htmlspecialchars($b['title'])?></td>
        <td><?=htmlspecialchars($b['author'])?></td>
        <td><?=htmlspecialchars($b['isbn'])?></td>
        <td><?=$b['copies']?></td>
        <td><?=$b['available']?></td>
        <td>
          <a href="edit_book.php?id=<?=$b['id']?>">Edit</a> |
          <a href="delete_book.php?id=<?=$b['id']?>" onclick="return confirm('Delete?')">Delete</a> |
          <a href="issue_book.php?id=<?=$b['id']?>">Issue</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</body>
</html>
```

---

## public/add_book.php

```php
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $copies = (int)$_POST['copies'];

    $stmt = $pdo->prepare('INSERT INTO books (title,author,isbn,copies,available) VALUES (?,?,?,?,?)');
    $stmt->execute([$title,$author,$isbn,$copies,$copies]);
    header('Location: books.php'); exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Add Book</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
  <div class="container">
    <h2>Add Book</h2>
    <form method="post">
      <label>Title</label><input name="title" required>
      <label>Author</label><input name="author">
      <label>ISBN</label><input name="isbn">
      <label>Copies</label><input name="copies" type="number" min="1" value="1">
      <button type="submit">Save</button>
    </form>
  </div>
</body>
</html>
```

---

## public/issue_book.php

```php
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$book_id = (int)($_GET['id'] ?? 0);
// fetch book
$bookStmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$bookStmt->execute([$book_id]);
$book = $bookStmt->fetch();

// fetch students
$students = $pdo->query('SELECT id, name, roll FROM students ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    // create issue
    $stmt = $pdo->prepare('INSERT INTO issues (book_id, student_id, issue_date, due_date) VALUES (?,?,?,?)');
    $stmt->execute([$book_id, $student_id, $issue_date, $due_date]);

    // decrement available
    $pdo->prepare('UPDATE books SET available = available - 1 WHERE id = ?')->execute([$book_id]);
    header('Location: books.php'); exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Issue Book</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
  <div class="container">
    <h2>Issue Book: <?=htmlspecialchars($book['title'])?></h2>
    <form method="post">
      <label>Student</label>
      <select name="student_id" required>
        <?php foreach($students as $s): ?>
          <option value="<?=$s['id']?>"><?=htmlspecialchars($s['roll'] . ' - ' . $s['name'])?></option>
        <?php endforeach; ?>
      </select>
      <label>Issue Date</label><input type="date" name="issue_date" required value="<?=date('Y-m-d')?>">
      <label>Due Date</label><input type="date" name="due_date" required>
      <button type="submit">Issue</button>
    </form>
  </div>
</body>
</html>
```

---

## public/return_book.php (simple handler)

```php
<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$issue_id = (int)($_GET['issue_id'] ?? 
```
