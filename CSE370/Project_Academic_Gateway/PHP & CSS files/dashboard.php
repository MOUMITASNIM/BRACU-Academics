<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];

function renderContentByRole($role) {
    switch ($role) {
        case 'admin':
            return "<h3>Admin Panel</h3><p>Manage users and settings.</p>";
        case 'teacher':
            return "<h3>Teacher Dashboard</h3></br><p>View and manage class materials.</p>";
        case 'student':
            return "<h3>Student Dashboard</h3><p>Access your courses and assignments.</p>";
        default:
            return "<p>Role not recognized.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?= htmlspecialchars($user['username']) ?>!</h2>
    <?= renderContentByRole($user['role']) ?>
    <a href="logout.php">Logout</a>
</body>
</html>
