<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $note_title = $_POST['note_title'];
    $uploaded_by = $_SESSION['user']['id'];

    // Handle file upload
    if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['note_file']['tmp_name'];
        $file_name = $_FILES['note_file']['name'];
        $file_dest = 'uploads/' . $file_name;

        if (move_uploaded_file($file_tmp, $file_dest)) {
            $stmt = $pdo->prepare("INSERT INTO lecture_notes (course_id, note_title, note_file, uploaded_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$course_id, $note_title, $file_dest, $uploaded_by]);
            echo "Lecture note uploaded successfully.";
        } else {
            echo "Error uploading file.";
        }
    }
}
?>
