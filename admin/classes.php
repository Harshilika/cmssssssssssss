<?php include('../includes/config.php') ?>
<?php include('header.php') ?>
<?php include('sidebar.php') ?>

<?php
if (isset($_POST['submit'])) {
    $class_name = $_POST['class_name'];

    // Validate input
    if (empty($class_name)) {
        die('Class name is required.');
    }

    // Insert new class into the database
    $query = mysqli_query($db_conn, "INSERT INTO `classes`(`title`, `added_date`) VALUES ('$class_name', NOW())");

    if (!$query) {
        die('DB error: ' . mysqli_error($db_conn));
    }
}

// Fetch existing classes
$classes_query = mysqli_query($db_conn, "SELECT * FROM `classes`");
if (!$classes_query) {
    die('DB error: ' . mysqli_error($db_conn));
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Classes</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Classes</li>
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
                        <h3 class="card-title">Classes</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive bg-white">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Added Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($class = mysqli_fetch_object($classes_query)) { ?>
                                        <tr>
                                            <td><?= $class->id ?></td>
                                            <td><?= htmlspecialchars($class->title) ?></td>
                                            <td><?= htmlspecialchars($class->added_date) ?></td>
                                            <td>
                                                <a href="edit_class.php?id=<?= $class->id ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="delete_class.php?id=<?= $class->id ?>" class="btn btn-danger btn-sm">Delete</a>
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
                        <h3 class="card-title">Add New Class</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="class_name">Class Name</label>
                                <input type="text" name="class_name" placeholder="Class Name" required class="form-control">
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
