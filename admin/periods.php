<?php include('../includes/config.php'); ?>

<?php

if (isset($_POST['submit'])) {
    // Retrieve and sanitize user inputs
    $title = isset($_POST['title']) ? mysqli_real_escape_string($db_conn, $_POST['title']) : '';
    $from = isset($_POST['from']) ? mysqli_real_escape_string($db_conn, $_POST['from']) : '';
    $to = isset($_POST['to']) ? mysqli_real_escape_string($db_conn, $_POST['to']) : '';
    
    $status = 'publish';
    $type = 'period';
    $date_add = date('Y-m-d g:i:s');

    // Insert into posts table
    $query = mysqli_query($db_conn, "INSERT INTO `posts` (`title`,`status`,`publish_date`,`type`) VALUES ('$title','$status','$date_add','$type')");

    if ($query) {
        $item_id = mysqli_insert_id($db_conn);
        
        // Insert metadata
        mysqli_query($db_conn, "INSERT INTO `metadata` (`meta_key`,`meta_value`,`item_id`) VALUES ('from','$from','$item_id')");
        mysqli_query($db_conn, "INSERT INTO `metadata` (`meta_key`,`meta_value`,`item_id`) VALUES ('to','$to','$item_id')");
        
        // Redirect after successful insertion
        header('Location: periods.php');
        exit; // Always exit after a header redirect
    } else {
        echo "Error: " . mysqli_error($db_conn); // Display error if the query fails
    }
}
?>

<?php include('header.php'); ?>
<?php include('sidebar.php'); ?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Periods</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Periods</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class='col-lg-8'>
                <!-- Info boxes -->
                <div class="card">
                    <div class="card-header py-2">
                        <h3 class="card-title">Periods</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive bg-white">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Title</th>
                                        <th>From</th>
                                        <th>To</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    $args = array(
                                        'type' => 'period',
                                        'status' => 'publish',
                                    );
                                    $periods = get_posts($args);
                                    foreach ($periods as $period) {
                                        $from = get_metadata($period->id, 'from')[0]->meta_value;
                                        $to = get_metadata($period->id, 'to')[0]->meta_value;
                                        ?>
                                        <tr>
                                            <td><?= $count++ ?></td>
                                            <td><?= htmlspecialchars($period->title) ?></td>
                                            <td><?= date('h:i A', strtotime($from)) ?></td>
                                            <td><?= date('h:i A', strtotime($to)) ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Add New Period -->
                <div class="card">
                    <div class="card-header py-2">
                        <h3 class="card-title">Add New Period</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" placeholder="Title" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="from">From</label>
                                <input type="time" name="from" placeholder="From" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="to">To</label>
                                <input type="time" name="to" placeholder="To" required class="form-control">
                            </div>
                            <button name="submit" class="btn btn-success float-right">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
