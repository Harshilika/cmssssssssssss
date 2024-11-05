<?php 
include('../includes/config.php');
include('header.php');
include('sidebar.php');

// Start session only if it hasn't been started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for logged-in parent or student
if (!isset($_SESSION['parent_id']) && !isset($_SESSION['student_id'])) {
    echo "<div class='alert alert-danger'>You must be logged in to view this page.</div>";
    exit();
}

// Determine the class_id based on the logged-in user
$class_id = null;

if (isset($_SESSION['student_id'])) {
    // Fetch student's class from the accounts table
    $student_id = $_SESSION['student_id'];
    $student_query = mysqli_query($db_conn, "SELECT class_id FROM accounts WHERE id = '$student_id'");
    $class_id = mysqli_fetch_assoc($student_query)['class_id'];
} elseif (isset($_SESSION['parent_id'])) {
    // Fetch parent's child's class from the accounts table
    $parent_id = $_SESSION['parent_id'];
    $child_query = mysqli_query($db_conn, "
        SELECT class_id FROM accounts 
        WHERE id = (SELECT child_id FROM parents WHERE id = '$parent_id')
    ");
    $class_id = mysqli_fetch_assoc($child_query)['class_id'];
}

// Fetch the class name based on class_id
$class_name_query = mysqli_query($db_conn, "SELECT title FROM classes WHERE id = '$class_id'");
$class_name = mysqli_fetch_assoc($class_name_query)['title'];

// Content Header (Page header)
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Manage Time Table</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Parent/Student</a></li>
                    <li class="breadcrumb-item active">Time Table</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <!-- Display Timetable for Class -->
        <div class="card">
            <div class="card-body">
                <h3>Timetable for Class: <?= htmlspecialchars($class_name) ?></h3> <!-- Display class name -->
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
                        // Define the time slots
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

                        foreach ($time_slots as $time_slot) {
                            echo "<tr>";
                            echo "<td>{$time_slot}</td>"; // Display time slot

                            // Loop through days to fetch timetable for each day
                            foreach ($days as $day) {
                                // Query to fetch item_id for the specific class, day, and time slot
                                $query = mysqli_query($db_conn, "
                                    SELECT item_id FROM metadata 
                                    WHERE item_id = '$class_id' 
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
                                    
                                    // Fetch the course ID based on the retrieved item_id
                                    $course_id_query = mysqli_query($db_conn, "
                                        SELECT meta_value FROM metadata 
                                        WHERE meta_key = 'course_id' 
                                        AND item_id = '{$row['item_id']}'
                                    ");
                                    
                                    if ($course_id_query && mysqli_num_rows($course_id_query) > 0) {
                                        $course_id = mysqli_fetch_assoc($course_id_query)['meta_value'];
                                        $course_query = mysqli_query($db_conn, "SELECT * FROM courses WHERE id = '$course_id'");
                                        
                                        // Check if the course exists
                                        if ($course_query && mysqli_num_rows($course_query) > 0) {
                                            $course = mysqli_fetch_assoc($course_query);
                                            // Display course name
                                            echo htmlspecialchars($course['name']);
                                        } else {
                                            echo "Course not found";
                                        }
                                    } else {
                                        echo "Course ID not found";
                                    }
                                } else {
                                    echo "No class";
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

    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
