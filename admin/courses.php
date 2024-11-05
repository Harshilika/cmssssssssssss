<?php include('../includes/config.php') ?>
<?php include('header.php') ?>
<?php include('sidebar.php') ?>

<?php
if (isset($_POST['submit'])) {
    $course_name = $_POST['course_name'];
    $category = $_POST['category'];
    $duration = $_POST['duration'];
    $date = $_POST['date'];
    $image = $_POST['image'];

    // Validate input
    if (empty($course_name) || empty($category) || empty($duration) || empty($date) || empty($image)) {
        die('All fields are required.');
    }

    // Insert new course into the database
    $query = mysqli_query($db_conn, "INSERT INTO `courses`(`name`, `category`, `duration`, `date`, `image`) VALUES ('$course_name', '$category', '$duration', '$date', '$image')");

    if (!$query) {
        die('DB error: ' . mysqli_error($db_conn));
    }
}

// Fetch existing courses
$courses_query = mysqli_query($db_conn, "SELECT * FROM `courses`");
if (!$courses_query) {
    die('DB error: ' . mysqli_error($db_conn));
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Courses</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Courses</li>
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
                        <h3 class="card-title">Courses</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive bg-white">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Duration</th>
                                        <th>Date</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($course = mysqli_fetch_object($courses_query)) { ?>
                                        <tr>
                                            <td><?= $course->id ?></td>
                                            <td><?= htmlspecialchars($course->name) ?></td>
                                            <td><?= htmlspecialchars($course->category) ?></td>
                                            <td><?= htmlspecialchars($course->duration) ?></td>
                                            <td><?= htmlspecialchars($course->date) ?></td>
                                            <td><?= htmlspecialchars($course->image) ?></td>
                                            <td>
                                                <a href="edit.php?id=<?= $course->id ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="delete.php?id=<?= $course->id ?>" class="btn btn-danger btn-sm">Delete</a>
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
                        <h3 class="card-title">Add New Course</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="course_name">Course Name</label>
                                <input type="text" name="course_name" placeholder="Course Name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" name="category" placeholder="Category" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration</label>
                                <input type="text" name="duration" placeholder="Duration" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="date">Date</label>
                                <input type="datetime-local" name="date" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="image">Image</label>
                                <input type="text" name="image" placeholder="Image URL" required class="form-control">
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
