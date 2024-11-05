<?php include('../includes/config.php') ?>
<?php include('header.php') ?>
<?php include('sidebar.php') ?>

<?php
if (isset($_POST['submit'])) {
    $title = $_POST['title'];

    // Validate input
    if (empty($title)) {
        die('All fields are required.');
    }

    // Insert new section into the database
    $query = mysqli_query($db_conn, "INSERT INTO `sections`(`title`) VALUES ('$title')");

    if (!$query) {
        die('DB error: ' . mysqli_error($db_conn));
    }
}

// Fetch existing sections
$sections_query = mysqli_query($db_conn, "SELECT * FROM `sections`");
if (!$sections_query) {
    die('DB error: ' . mysqli_error($db_conn));
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Sections</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Sections</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class='col-lg-8'>
                <div class="card">
                    <div class="card-header py-2">
                        <h3 class="card-title">Sections</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive bg-white">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($section = mysqli_fetch_object($sections_query)) { ?>
                                        <tr>
                                            <td><?= $section->id ?></td>
                                            <td><?= htmlspecialchars($section->title) ?></td>
                                            <td>
                                                <a href="edit_section.php?id=<?= $section->id ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="delete_section.php?id=<?= $section->id ?>" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header py-2">
                        <h3 class="card-title">Add New Section</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="title">Section Title</label>
                                <input type="text" name="title" placeholder="Section Title" required class="form-control">
                            </div>
                            <button name="submit" class="btn btn-success float-right">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php') ?>
