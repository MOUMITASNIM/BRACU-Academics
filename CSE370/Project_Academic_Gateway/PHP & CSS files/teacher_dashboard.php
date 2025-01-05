<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

$teacher_id = $_SESSION['user']['id'];

// Fetch all courses
$courses_stmt = $pdo->query("SELECT * FROM courses");
$courses = $courses_stmt->fetchAll();

// Fetch courses assigned to this teacher
$assigned_stmt = $pdo->prepare("SELECT courses.* FROM courses 
                                JOIN teacher_courses ON courses.id = teacher_courses.course_id
                                WHERE teacher_courses.teacher_id = ?");
$assigned_stmt->execute([$teacher_id]);
$assigned_courses = $assigned_stmt->fetchAll();

// Assign a course to the teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("INSERT INTO teacher_courses (teacher_id, course_id) VALUES (?, ?)");
    $stmt->execute([$teacher_id, $course_id]);

    header("Location: teacher_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Teacher Dashboard</title>
    <style>
        .logout-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .logout-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Teacher Dashboard</h2>
        <form method="POST" action="logout.php" style="display: inline-block;">
            <button class="logout-btn" type="submit">Logout</button>
        </form>

        <h3>Assign Courses to Teach</h3>
        <form method="POST">
            <select name="course_id" required>
                <option value="">Select a Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Assign Course</button>
        </form>

        <h3>Your Courses</h3>
        <ul>
            <?php foreach ($assigned_courses as $course): ?>
                <li><?= htmlspecialchars($course['course_name']) ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>Upload Lecture Notes</h3>
        <form method="POST" action="upload_notes.php" enctype="multipart/form-data">
            <select name="course_id" required>
                <option value="">Select a Course</option>
                <?php foreach ($assigned_courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="note_title" placeholder="Note Title" required>
            <input type="file" name="note_file" accept=".pdf,.doc,.docx,.ppt,.pptx" required>
            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>