<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the course details for confirmation
    $course_query = mysqli_query($db_conn, "SELECT * FROM `courses` WHERE `id` = $id");
    if (!$course_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $course = mysqli_fetch_object($course_query);
    if (!$course) {
        die('Course not found.');
    }
}

if (isset($_POST['delete'])) {
    // Delete the course from the database
    $delete_query = mysqli_query($db_conn, "DELETE FROM `courses` WHERE `id` = $id");

    if (!$delete_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    header('Location: sections.php'); // Redirect back to the sections page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Course</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Delete Course</h1>
        <p class="text-center">Are you sure you want to delete the course <strong><?= htmlspecialchars($course->name) ?></strong>?</p>
        <form action="" method="POST" class="text-center">
            <button name="delete" class="btn btn-danger">Delete</button>
            <a href="sections.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
