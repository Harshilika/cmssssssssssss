<?php 
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection code goes here...
include('../includes/config.php'); 

// Ensure the user is logged in as a student
if (!isset($_SESSION['student_id'])) {
    echo "<div class='alert alert-danger'>You must be logged in to view this page.</div>";
    exit();
}

// The rest of your attendance.php code...
// Get student ID
$student_id = $_SESSION['student_id'];

// Fetch attendance for the logged-in student
$attendance_sql = "SELECT attendance_month, attendance_value, course_id FROM attendance WHERE std_id = '$student_id'";
$attendance_query = mysqli_query($db_conn, $attendance_sql);
$attendance_records = mysqli_fetch_all($attendance_query, MYSQLI_ASSOC);

// Calculate attendance summary
$attendance_summary = [];
foreach ($attendance_records as $record) {
    $month = date('F Y', strtotime($record['attendance_month']));
    $status = $record['attendance_value'];

    if (!isset($attendance_summary[$month])) {
        $attendance_summary[$month] = ['Present' => 0, 'Absent' => 0];
    }

    if ($status == 'Present') {
        $attendance_summary[$month]['Present']++;
    } else {
        $attendance_summary[$month]['Absent']++;
    }
}

// Calculate total attendance and percentage for each month
$total_classes = 0;
$total_present = 0;
$total_absent = 0;

foreach ($attendance_summary as $summary) {
    $total_present += $summary['Present'];
    $total_absent += $summary['Absent'];
    $total_classes += ($summary['Present'] + $summary['Absent']);
}

$attendance_percentage = $total_classes > 0 ? ($total_present / $total_classes) * 100 : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Attendance</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Your Attendance</h1>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Month</th>
                    <th>No of Classes Present</th>
                    <th>No of Classes Absent</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_summary as $month => $summary): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($month); ?></td>
                        <td><?php echo $summary['Present']; ?></td>
                        <td><?php echo $summary['Absent']; ?></td>
                        <td>
                            <?php 
                            $total_days = $summary['Present'] + $summary['Absent'];
                            echo $total_days > 0 ? number_format(($summary['Present'] / $total_days) * 100, 2) . '%' : 'N/A'; 
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($attendance_summary)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No attendance records found.</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong><?php echo $total_present; ?></strong></td>
                    <td><strong><?php echo $total_absent; ?></strong></td>
                    <td><strong><?php echo number_format($attendance_percentage, 2) . '%'; ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
