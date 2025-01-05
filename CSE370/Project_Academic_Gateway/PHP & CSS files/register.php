<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Step 1: Validate email
    if (empty($email)) {
        echo "<p>Email cannot be empty.</p>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<p>Invalid email format.</p>";
        exit();
    }

    // Step 2: Check if email already exists in the database
    $check_email_stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ?");
    $check_email_stmt->execute([$email]);

    if ($check_email_stmt->rowCount() > 0) {
        echo "<p>This email is already registered. Please use another email.</p>";
        exit();
    }

    // Step 3: Insert user into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $username, $password, $role]);

        // Fetch the newly registered user
        $user_stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $user_stmt->execute([$email]);
        $user = $user_stmt->fetch();

        // Automatically log in the user
        $_SESSION['user'] = $user;

        // Redirect to the appropriate dashboard based on role
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'teacher') {
            header("Location: teacher_dashboard.php");
        } elseif ($user['role'] === 'student') {
            header("Location: student_dashboard.php");
        }
        exit();
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="" disabled selected>Select your role</option>
            <option value="admin">Admin</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
        </select>
        <button type="submit">Register</button>
    </form>
</body>
</html>


