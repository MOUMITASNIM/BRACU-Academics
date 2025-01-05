<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$student_id = $_SESSION['user']['id'];

// Fetch all courses
$courses_stmt = $pdo->query("SELECT * FROM courses");
$courses = $courses_stmt->fetchAll();

// Fetch enrolled courses
$enrolled_stmt = $pdo->prepare("SELECT courses.* FROM courses 
                                JOIN student_courses ON courses.id = student_courses.course_id
                                WHERE student_courses.student_id = ?");
$enrolled_stmt->execute([$student_id]);
$enrolled_courses = $enrolled_stmt->fetchAll();

// Enroll in a course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
    $stmt->execute([$student_id, $course_id]);

    header("Location: student_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Student Dashboard</title>
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
        <h2>Student Dashboard</h2>
        <form method="POST" action="logout.php" style="display: inline-block;">
            <button class="logout-btn" type="submit">Logout</button>
        </form>

        <h3>Enroll in a Course</h3>
        <form method="POST">
            <select name="course_id" required>
                <option value="">Select a Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Enroll</button>
        </form>

        <h3>Your Courses</h3>
        <ul>
            <?php foreach ($enrolled_courses as $course): ?>
                <li><?= htmlspecialchars($course['course_name']) ?></li>
            <?php endforeach; ?>
        </ul>

        <h3>Lecture Notes</h3>
        <ul>
            <?php
            $notes_stmt = $pdo->prepare("SELECT * FROM lecture_notes WHERE course_id IN (SELECT course_id FROM student_courses WHERE student_id = ?)");
            $notes_stmt->execute([$student_id]);
            $notes = $notes_stmt->fetchAll();

            foreach ($notes as $note): ?>
                <li>
                    <strong><?= htmlspecialchars($note['note_title']) ?></strong>
                    <a href="<?= htmlspecialchars($note['note_file']) ?>" target="_blank">Download</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>

