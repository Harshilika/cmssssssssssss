<?php include('../includes/config.php'); ?>
<?php include('header.php'); ?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Available Courses</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch existing courses
                                    $courses_query = mysqli_query($db_conn, "SELECT * FROM `courses`");
                                    if (!$courses_query) {
                                        die('DB error: ' . mysqli_error($db_conn));
                                    }

                                    while ($course = mysqli_fetch_object($courses_query)) { ?>
                                        <tr>
                                            <td><?= $course->id ?></td>
                                            <td><?= htmlspecialchars($course->name) ?></td>
                                            <td><?= htmlspecialchars($course->category) ?></td>
                                            <td><?= htmlspecialchars($course->duration) ?></td>
                                            <td><?= htmlspecialchars($course->date) ?></td>
                                            <td><img src="<?= htmlspecialchars($course->image) ?>" alt="<?= htmlspecialchars($course->name) ?>" style="width: 100px;"></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>
