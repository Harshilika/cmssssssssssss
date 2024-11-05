<?php 
include('../includes/config.php'); // Include your database configuration

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attendance'])) {
    $attendance_date = date('Y-m-d', strtotime($_POST['attendance_date']));
    $class_id = $_POST['class_id'];

    foreach ($_POST['attendance'] as $student_id => $status) {
        // Check if the attendance already exists
        $check_sql = "SELECT * FROM attendance WHERE std_id = $student_id AND attendance_month = '$attendance_date'";
        $check_query = mysqli_query($db_conn, $check_sql);

        if (mysqli_num_rows($check_query) > 0) {
            // Update existing attendance record
            $update_sql = "UPDATE attendance SET attendance_value = '$status', modified_date = NOW() WHERE std_id = $student_id AND attendance_month = '$attendance_date'";
            mysqli_query($db_conn, $update_sql);
        } else {
            // Insert new attendance record
            $insert_sql = "INSERT INTO attendance (std_id, attendance_month, attendance_value, modified_date) VALUES ($student_id, '$attendance_date', '$status', NOW())";
            mysqli_query($db_conn, $insert_sql);
        }
    }

    echo "<div class='alert alert-success'>Attendance recorded successfully!</div>";
}

// Fetch classes
$classes_sql = "SELECT * FROM classes";
$classes_query = mysqli_query($db_conn, $classes_sql);

// Fetch students based on class selection
$students = [];
$class_id = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $students_sql = "SELECT * FROM accounts WHERE type = 'student' AND class_id = $class_id"; 
    $students_query = mysqli_query($db_conn, $students_sql);
    while ($student = mysqli_fetch_assoc($students_query)) {
        $students[] = $student;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Attendance</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Add Attendance</h1>

        <form action="" method="POST" class="mb-4">
            <div class="form-group">
                <label for="class_id">Select Class:</label>
                <select name="class_id" id="class_id" class="form-control" required>
                    <option value="">--Select Class--</option>
                    <?php while ($class = mysqli_fetch_assoc($classes_query)): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['title']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="attendance_date">Attendance Date:</label>
                <input type="date" name="attendance_date" id="attendance_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">View Students</button>
        </form>

        <?php if (!empty($students)): ?>
            <form action="" method="POST">
                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo $_POST['attendance_date']; ?>">
                
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Present</th>
                            <th>Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td>
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="Present" required>
                                </td>
                                <td>
                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="Absent" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-success">Submit Attendance</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
