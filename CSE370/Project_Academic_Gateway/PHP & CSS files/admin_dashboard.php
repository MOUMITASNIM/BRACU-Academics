<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['course_name']) && isset($_POST['course_description'])) {
        $course_name = $_POST['course_name'];
        $course_description = $_POST['course_description'];

        // Insert course details into the courses table
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_description) VALUES (?, ?)");
        $stmt->execute([$course_name, $course_description]);

        $success_message = "Course successfully uploaded.";
    }

    // Exam Upload Section
    if (isset($_POST['exam_name']) && isset($_POST['exam_date']) && isset($_POST['exam_description']) && isset($_POST['course_id'])) {
        $exam_name = $_POST['exam_name'];
        $exam_date = $_POST['exam_date'];
        $exam_description = $_POST['exam_description'];
        $course_id = $_POST['course_id'];

        // Insert exam details into the exams table
        $teacher_id = $_SESSION['user']['id'];
        $stmt = $pdo->prepare("INSERT INTO exams (exam_name, exam_date, exam_description, course_id, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$exam_name, $exam_date, $exam_description, $course_id, $teacher_id]);

        $exam_success_message = "Exam successfully uploaded.";
    }

    // Grade Upload Section
    if (isset($_POST['student_id']) && isset($_POST['exam_id']) && isset($_POST['grade'])) {
        $student_id = $_POST['student_id'];
        $exam_id = $_POST['exam_id'];
        $grade = $_POST['grade'];

        // Insert grade details into the grades table
        $stmt = $pdo->prepare("INSERT INTO grades (student_id, exam_id, grade) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $exam_id, $grade]);

        $grade_success_message = "Grade successfully uploaded.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2, h3 {
            color: #2c3e50;
        }
        form {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input, textarea, select, button {
            display: block;
            margin-bottom: 10px;
            padding: 8px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #2c3e50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #34495e;
        }
        p {
            color: green;
        }
        .logout {
            color: white;
            background-color: red;
            border: none;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout:hover {
            background-color: darkred;
        }
    </style>
    <title>Admin Dashboard</title>
</head>
<body>
    <h2>Admin Dashboard</h2>

    <a href="logout.php" class="logout">Logout</a>

    <!-- Course Upload Section -->
    <h3>Upload New Course</h3>
    <?php if (isset($success_message)) echo "<p>$success_message</p>"; ?>
    <form method="POST">
        <input type="text" name="course_name" placeholder="Course Name" required>
        <textarea name="course_description" placeholder="Course Description" required></textarea>
        <button type="submit">Upload Course</button>
    </form>

    <!-- Exam Upload Section -->
    <h3>Upload New Exam</h3>
    <?php if (isset($exam_success_message)) echo "<p>$exam_success_message</p>"; ?>
    <form method="POST">
        <input type="text" name="exam_name" placeholder="Exam Name" required>
        <input type="date" name="exam_date" required>
        <textarea name="exam_description" placeholder="Exam Description" required></textarea>
        <select name="course_id" required>
            <option value="">Select Course</option>
            <?php
            // Fetch courses from the database
            $courses_stmt = $pdo->query("SELECT id, course_name FROM courses");
            while ($course = $courses_stmt->fetch()) {
                echo "<option value='" . $course['id'] . "'>" . htmlspecialchars($course['course_name']) . "</option>";
            }
            ?>
        </select>
        <button type="submit">Upload Exam</button>
    </form>

    <!-- Grade Upload Section -->
    <h3>Upload Grades</h3>
    <?php if (isset($grade_success_message)) echo "<p>$grade_success_message</p>"; ?>
    <form method="POST">
        <select name="student_id" required>
            <option value="">Select Student</option>
            <?php
            $students_stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'student'");
            while ($student = $students_stmt->fetch()) {
                echo "<option value='" . $student['id'] . "'>" . htmlspecialchars($student['username']) . "</option>";
            }
            ?>
        </select>
        <select name="exam_id" required>
            <option value="">Select Exam</option>
            <?php
            $exams_stmt = $pdo->query("SELECT id, exam_name FROM exams");
            while ($exam = $exams_stmt->fetch()) {
                echo "<option value='" . $exam['id'] . "'>" . htmlspecialchars($exam['exam_name']) . "</option>";
            }
            ?>
        </select>
        <input type="number" name="grade" placeholder="Grade (e.g., 85.5)" step="0.01" required>
        <button type="submit">Upload Grade</button>
    </form>
</body>
</html>