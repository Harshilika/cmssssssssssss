<?php
include('../includes/config.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch the current section details
    $section_query = mysqli_query($db_conn, "SELECT * FROM `sections` WHERE `id` = $id");
    if (!$section_query) {
        die('DB error: ' . mysqli_error($db_conn));
    }

    $section = mysqli_fetch_object($section_query);
    if (!$section) {
        die('Section not found.');
    }
}

if (isset($_POST['update'])) {
    $title = $_POST['title'];

    // Validate input
    if (empty($title)) {
        die('All fields are required.');
    }

    // Update the section in the database
    $update_query = mysqli_query($db_conn, "UPDATE `sections` SET `title`='$title' WHERE `id` = $id");

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
    <title>Edit Section</title>
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
        <h1 class="text-center">Edit Section</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="title">Section Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($section->title) ?>" required>
            </div>
            <button name="update" class="btn btn-primary btn-block">Update</button>
        </form>
    </div>
</body>
</html>
