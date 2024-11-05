<?php
// Include the necessary files and start the session
include('../includes/config.php');
include('header.php');
include('sidebar.php');

// Check the user role; allow only 'student' roles
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'student') {
    // Redirect to the login page if the user is not authorized
    header("Location: login.php");
    exit;
}
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Upcoming Campus Functions</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Student</a></li>
                    <li class="breadcrumb-item active">Upcoming Functions</li>
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
                <h2>Upcoming Campus Functions</h2>

                <?php
                // Get the current date
                $current_date = date('Y-m-d');

                // Query to get upcoming campus functions (functions happening today or in the future)
                $query = "SELECT * FROM campus_functions WHERE function_date >= '$current_date' ORDER BY function_date ASC";
                $result = mysqli_query($db_conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    echo "<table class='table'>";
                    echo "<thead><tr><th>Title</th><th>Description</th><th>Date</th><th>Time</th></tr></thead>";
                    echo "<tbody>";

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['function_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['function_time_from']) . " - " . htmlspecialchars($row['function_time_to']) . "</td>";
                        echo "</tr>";
                    }

                    echo "</tbody></table>";
                } else {
                    echo "<div class='alert alert-warning'>No upcoming campus functions available.</div>";
                }
                ?>
            </div>
        </div>
    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
