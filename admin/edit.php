<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the current course details
    $course_query = mysqli_query($db_conn, "SELECT * FROM `courses` WHERE `id` = $id");
    if (!$course_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $course = mysqli_fetch_object($course_query);
    if (!$course) {
        die('Course not found.');
    }
}

if (isset($_POST['update'])) {
    $course_name = $_POST['course_name'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    $date = $_POST['date'];
    $image = $_POST['image'];

    // Validate input
    if (empty($course_name) || empty($category) || empty($duration) || empty($date) || empty($image)) {
        die('All fields are required.');
    }

    // Update the course in the database
    $update_query = mysqli_query($db_conn, "UPDATE `courses` SET 
        `name`='$course_name', 
        `category`='$category', 
        `duration`='$duration', 
        `date`='$date', 
        `image`='$image' 
        WHERE `id` = $id");

    if (!$update_query) {
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
    <title>Edit Course</title>
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
        <h1 class="text-center">Edit Course</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" name="course_name" class="form-control" value="<?= htmlspecialchars($course->name) ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($course->category) ?>" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($course->duration) ?>" required>
            </div>
            <div class="form-group">
                <label for="date">Date</label>
                <input type="datetime-local" name="date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($course->date)) ?>" required>
            </div>
            <div class="form-group">
                <label for="image">Image URL</label>
                <input type="text" name="image" class="form-control" value="<?= htmlspecialchars($course->image) ?>" required>
            </div>
            <button name="update" class="btn btn-primary btn-block">Update</button>
        </form>
    </div>
</body>
</html>
