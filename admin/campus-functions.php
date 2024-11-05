<?php include('../includes/config.php'); ?>
<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Campus Functions</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Campus Functions</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h2>Add Campus Function</h2>

                <?php
                // Check for form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
                    // Get form data
                    $function_title = mysqli_real_escape_string($db_conn, $_POST['function_title']);
                    $function_description = mysqli_real_escape_string($db_conn, $_POST['function_description']);
                    $function_date = mysqli_real_escape_string($db_conn, $_POST['function_date']);
                    $function_time_from = mysqli_real_escape_string($db_conn, $_POST['function_time_from']);
                    $function_time_to = mysqli_real_escape_string($db_conn, $_POST['function_time_to']);

                    // Insert into the database
                    $query = "INSERT INTO campus_functions (title, description, function_date, function_time_from, function_time_to) 
                              VALUES ('$function_title', '$function_description', '$function_date', '$function_time_from', '$function_time_to')";

                    if (mysqli_query($db_conn, $query)) {
                        echo "<div class='alert alert-success'>Function added successfully!</div>";
                    } else {
                        echo "<div class='alert alert-danger'>Error: " . mysqli_error($db_conn) . "</div>";
                    }
                }
                ?>

                <!-- Form to add campus function -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="function_title">Function Title:</label>
                        <input type="text" name="function_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="function_description">Description:</label>
                        <textarea name="function_description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="function_date">Date:</label>
                        <input type="date" name="function_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="function_time_from">From Time:</label>
                        <input type="time" name="function_time_from" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="function_time_to">To Time:</label>
                        <input type="time" name="function_time_to" class="form-control" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Add Function</button>
                </form>
            </div>
        </div>
    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
