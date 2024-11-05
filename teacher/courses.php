<?php include('../includes/config.php'); ?>
<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Available Courses</h1>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title">Courses</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive bg-white">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Duration</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            $course_query = mysqli_query($db_conn, 'SELECT * FROM courses');
                            while ($course = mysqli_fetch_object($course_query)) { ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><img src="../dist/uploads/<?= $course->image ?>" height="100" alt="<?= $course->name ?>" class="border"></td>
                                    <td><?= $course->name ?></td>
                                    <td><?= $course->category ?></td>
                                    <td><?= $course->duration ?></td>
                                    <td><?= $course->date ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
