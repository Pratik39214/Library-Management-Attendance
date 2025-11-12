<?php
/**
 * Header Include File
 * Contains HTML head section and navigation menu
 */
if (!defined('DB_HOST')) {
    require_once 'config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-book"></i>
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a></li>
                <li><a href="books.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Books
                </a></li>
                <li><a href="students.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-graduate"></i> Students
                </a></li>
                <li><a href="issue_book.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i> Issue/Return
                </a></li>
                <li><a href="attendance.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> Attendance
                </a></li>
                <li><a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a></li>
                <li class="nav-user">
                    <a href="#" class="user-dropdown">
                        <i class="fas fa-user-circle"></i> 
                        <?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?>
                        <i class="fas fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content Container -->
    <div class="main-container">
        <?php
        // Display success message if exists
        $successMsg = getSuccessMessage();
        if (!empty($successMsg)):
        ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $successMsg; ?>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>
        
        <?php
        // Display error message if exists
        $errorMsg = getErrorMessage();
        if (!empty($errorMsg)):
        ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $errorMsg; ?>
            <button class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        <?php endif; ?>
