<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the section details for confirmation
    $section_query = mysqli_query($db_conn, "SELECT * FROM `sections` WHERE `id` = $id");
    if (!$section_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $section = mysqli_fetch_object($section_query);
    if (!$section) {
        die('Section not found.');
    }
}

if (isset($_POST['delete'])) {
    // Delete the section from the database
    $delete_query = mysqli_query($db_conn, "DELETE FROM `sections` WHERE `id` = $id");

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
    <title>Delete Section</title>
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
        <h1 class="text-center">Delete Section</h1>
        <p class="text-center">Are you sure you want to delete the section <strong><?= htmlspecialchars($section->title) ?></strong>?</p>
        <form action="" method="POST" class="text-center">
            <button name="delete" class="btn btn-danger">Delete</button>
            <a href="sections.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
