<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the class details for confirmation
    $class_query = mysqli_query($db_conn, "SELECT * FROM `classes` WHERE `id` = $id");
    if (!$class_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $class = mysqli_fetch_object($class_query);
    if (!$class) {
        die('Class not found.');
    }
}

if (isset($_POST['delete'])) {
    // Delete the class from the database
    $delete_query = mysqli_query($db_conn, "DELETE FROM `classes` WHERE `id` = $id");

    if (!$delete_query) {
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
    <title>Delete Class</title>
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
        <h1 class="text-center">Delete Class</h1>
        <p class="text-center">Are you sure you want to delete the class <strong><?= htmlspecialchars($class->title) ?></strong>?</p>
        <form action="" method="POST" class="text-center">
            <button name="delete" class="btn btn-danger">Delete</button>
            <a href="classes.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
