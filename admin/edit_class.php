<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the current class details
    $class_query = mysqli_query($db_conn, "SELECT * FROM `classes` WHERE `id` = $id");
    if (!$class_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $class = mysqli_fetch_object($class_query);
    if (!$class) {
        die('Class not found.');
    }
}

if (isset($_POST['update'])) {
    $class_title = $_POST['class_name']; // Update variable name to match your form field

    // Validate input
    if (empty($class_title)) {
        die('All fields are required.');
    }

    // Update the class in the database
    $update_query = mysqli_query($db_conn, "UPDATE `classes` SET `title`='$class_title' WHERE `id` = $id");

    if (!$update_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    header('Location: classes.php'); // Redirect back to the classes page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class</title>
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
        <h1 class="text-center">Edit Class</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="class_name">Class Title</label>
                <input type="text" name="class_name" class="form-control" value="<?= htmlspecialchars($class->title) ?>" required>
            </div>
            <button name="update" class="btn btn-primary btn-block">Update</button>
        </form>
    </div>
</body>
</html>
