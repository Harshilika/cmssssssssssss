<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get teacher ID from session
$teacher_id = $_SESSION['user_id']; // Ensure this has the correct session variable for the teacher's ID

// Fetch classes for the teacher
$class_query = mysqli_query($db_conn, "
    SELECT c.id, c.title FROM classes c 
    JOIN teacher_classes tc ON c.id = tc.class_id 
    WHERE tc.teacher_id = '$teacher_id'
");

// Get the selected class ID from URL or POST
$selected_class_id = isset($_GET['class_id']) ? $_GET['class_id'] : null;
$class_name = '';

// Fetch the class name if a class is selected
if ($selected_class_id) {
    $class_result = mysqli_query($db_conn, "SELECT title FROM classes WHERE id = '$selected_class_id'");
    $class_row = mysqli_fetch_assoc($class_result);
    if ($class_row) {
        $class_name = htmlspecialchars($class_row['title']);
    }
}

// Define the time slots and days
$time_slots = [
    '09:00 AM - 09:45 AM',
    '09:45 AM - 10:30 AM',
    '10:30 AM - 11:15 AM',
    '11:15 AM - 12:00 PM',
    '12:00 PM - 12:45 PM',
    '01:00 PM - 01:45 PM',
    '01:45 PM - 02:30 PM',
    '02:30 PM - 03:15 PM',
    '03:15 PM - 04:00 PM'
];
$days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
?>

<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Time Table</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Time Table</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Class Navigation Bar -->
        <div class="card mb-3">
            <div class="card-body">
                <h3>Select Class</h3>
                <div class="btn-group" role="group">
                    <?php while ($class = mysqli_fetch_assoc($class_query)): ?>
                        <a href="timetable.php?class_id=<?= $class['id'] ?>" 
                           class="btn btn-secondary <?= $selected_class_id == $class['id'] ? 'active' : '' ?>">
                           <?= htmlspecialchars($class['title']) ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- Display Timetable for Selected Class -->
        <?php if ($selected_class_id): ?>
            <div class="card">
                <div class="card-body">
                    <h3>Timetable for Class: <?= $class_name ?></h3>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Timing</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($time_slots as $time_slot) {
                                echo "<tr>";
                                echo "<td>{$time_slot}</td>"; // Display time slot

                                foreach ($days as $day) {
                                    $query = mysqli_query($db_conn, "
                                        SELECT * FROM metadata 
                                        WHERE item_id = '$selected_class_id' 
                                        AND meta_key = 'day_name' 
                                        AND meta_value = '$day' 
                                        AND item_id IN (
                                            SELECT item_id FROM metadata 
                                            WHERE meta_key = 'period_time' 
                                            AND meta_value = '$time_slot'
                                        )
                                    ");

                                    echo "<td>";
                                    if (mysqli_num_rows($query) > 0) {
                                        $row = mysqli_fetch_assoc($query);
                                        $course_id_result = mysqli_query($db_conn, "SELECT meta_value FROM metadata WHERE meta_key = 'course_id' AND item_id = '{$row['item_id']}'");
                                        $course_id_data = mysqli_fetch_assoc($course_id_result);
                                        
                                        if ($course_id_data && isset($course_id_data['meta_value'])) {
                                            $course_id = $course_id_data['meta_value'];
                                            $course = mysqli_fetch_assoc(mysqli_query($db_conn, "SELECT * FROM courses WHERE id = '$course_id'"));
                                            
                                            if ($course) {
                                                echo htmlspecialchars($course['name']);
                                            } else {
                                                echo "Course not found";
                                            }
                                        } else {
                                            echo "No course assigned";
                                        }
                                    } else {
                                        echo "No class scheduled";
                                    }
                                    echo "</td>";
                                }

                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Please select a class from the navigation bar to view its timetable.</div>
        <?php endif; ?>

    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
