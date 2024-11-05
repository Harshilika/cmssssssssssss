<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

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
        <?php
        // Fetch classes for the dropdown
        $class_query = mysqli_query($db_conn, "SELECT * FROM classes");

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

        // Handle form submission for updating timetable
        if (isset($_POST['update_timetable'])) {
            $class_id = $_POST['class_id'];

            foreach ($time_slots as $time_slot) {
                foreach ($days as $day) {
                    $course_id = $_POST["course_id_{$day}_{$time_slot}"] ?? '';

                    // Check if entry exists for the time slot and day
                    $check_query = mysqli_query($db_conn, "
                        SELECT * FROM metadata 
                        WHERE item_id = '$class_id' 
                        AND meta_key = 'day_name' 
                        AND meta_value = '$day' 
                        AND item_id IN (
                            SELECT item_id FROM metadata 
                            WHERE meta_key = 'period_time' 
                            AND meta_value = '$time_slot'
                        )
                    ");

                    if (mysqli_num_rows($check_query) > 0) {
                        // Update existing entry
                        mysqli_query($db_conn, "
                            UPDATE metadata 
                            SET meta_value = '$course_id' 
                            WHERE item_id = '$class_id' 
                            AND meta_key = 'course_id' 
                            AND item_id IN (
                                SELECT item_id FROM metadata 
                                WHERE meta_key = 'day_name' 
                                AND meta_value = '$day' 
                                AND item_id IN (
                                    SELECT item_id FROM metadata 
                                    WHERE meta_key = 'period_time' 
                                    AND meta_value = '$time_slot'
                                )
                            )
                        ");
                    } else {
                        // Insert new entry
                        mysqli_query($db_conn, "
                            INSERT INTO metadata (item_id, meta_key, meta_value) 
                            VALUES ('$class_id', 'day_name', '$day'), 
                                   ('$class_id', 'period_time', '$time_slot'), 
                                   ('$class_id', 'course_id', '$course_id')
                        ");
                    }
                }
            }
            echo '<div class="alert alert-success">Timetable updated successfully!</div>';
        }
        ?>

        <!-- Class Selection -->
        <div class="card">
            <div class="card-body">
                <h3>Select Class to View Timetable</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="class_id">Select Class</label>
                        <select name="class_id" id="class_id" class="form-control" required>
                            <option value="">-Select Class-</option>
                            <?php while ($class = mysqli_fetch_assoc($class_query)): ?>
                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <input type="submit" value="View Timetable" name="view_timetable" class="btn btn-primary">
                </form>
            </div>
        </div>

        <!-- Display Timetable for Selected Class -->
        <?php if (isset($_POST['view_timetable'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3>Timetable for Class ID: <?= htmlspecialchars($_POST['class_id']) ?></h3>

                    <form method="post">
                        <input type="hidden" name="class_id" value="<?= htmlspecialchars($_POST['class_id']) ?>">
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
                                            WHERE item_id = '{$_POST['class_id']}' 
                                            AND meta_key = 'day_name' 
                                            AND meta_value = '$day' 
                                            AND item_id IN (
                                                SELECT item_id FROM metadata 
                                                WHERE meta_key = 'period_time' 
                                                AND meta_value = '$time_slot'
                                            )
                                        ");

                                        echo "<td>";
                                        $course_name = '';
                                        if (mysqli_num_rows($query) > 0) {
                                            $row = mysqli_fetch_assoc($query);
                                            $course_query = mysqli_query($db_conn, "SELECT meta_value FROM metadata WHERE meta_key = 'course_name' AND item_id = '{$row['item_id']}'");
                                            
                                            if ($course_query && mysqli_num_rows($course_query) > 0) {
                                                $course_result = mysqli_fetch_assoc($course_query);
                                                $course_name = $course_result['meta_value'];
                                            }
                                        }

                                        // Single field that functions as a dropdown and text input
                                        echo "<input list='course_list_{$day}_{$time_slot}' name='course_id_{$day}_{$time_slot}' class='form-control' value='" . htmlspecialchars($course_name) . "'>";
                                        echo "<datalist id='course_list_{$day}_{$time_slot}'>";
                                        
                                        // Populate with courses
                                        $course_query = mysqli_query($db_conn, "SELECT * FROM courses");
                                        while ($course_option = mysqli_fetch_assoc($course_query)) {
                                            echo "<option value='" . htmlspecialchars($course_option['name']) . "'>";
                                        }
                                        echo "</datalist>";
                                        echo "</td>";
                                    }

                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <input type="submit" value="Update Timetable" name="update_timetable" class="btn btn-primary">
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div><!--/. container-fluid -->
</section>
<!-- /.content -->

<?php include('footer.php'); ?>
