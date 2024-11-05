<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Handle form submission for result recording
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['results'])) {
    if (is_array($_POST['results'])) { // Check if results is an array
        $class_id = $_POST['class_id'];
        $exam_type = $_POST['exam_type'];
        $subject_id = $_POST['subject_id'];

        // Loop through each course for each student to save their result
        foreach ($_POST['results'] as $student_id => $result) {
            // Set score to 0 if it’s empty or non-numeric
            $result = is_numeric($result) ? (float)$result : 0;

            // Insert or update result into the results table
            $insert_sql = "INSERT INTO results (student_id, course_id, exam_type, score) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE score = VALUES(score)";
            $insert_stmt = $db_conn->prepare($insert_sql);
            if ($insert_stmt) {
                $insert_stmt->bind_param("iisi", $student_id, $subject_id, $exam_type, $result);
                $insert_stmt->execute();

                if ($insert_stmt->error) {
                    echo "<div class='alert alert-danger'>Execution error: " . $insert_stmt->error . "</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Error preparing statement: " . $db_conn->error . "</div>";
            }
        }

        echo "<div class='alert alert-success'>Results recorded successfully!</div>";
    } else {
        echo "<div class='alert alert-warning'>No results to record.</div>";
    }
}

// Fetch classes for the teacher
$teacher_id = $_SESSION['user_id']; // Assuming the teacher's ID is stored in the session
$classes_sql = "SELECT c.* FROM classes c
                JOIN teacher_classes tc ON c.id = tc.class_id
                WHERE tc.teacher_id = ?";
$classes_stmt = $db_conn->prepare($classes_sql);
$classes_stmt->bind_param("i", $teacher_id);
$classes_stmt->execute();
$classes_query = $classes_stmt->get_result();

// Fetch subjects
$courses_sql = "SELECT * FROM courses"; 
$courses_query = mysqli_query($db_conn, $courses_sql);
$courses_list = mysqli_fetch_all($courses_query, MYSQLI_ASSOC); // Store courses in courses_list

// Variables to store selected class, exam type, and subject
$class_id = $_POST['class_id'] ?? '';
$exam_type = $_POST['exam_type'] ?? ''; 
$subject_id = $_POST['subject_id'] ?? '';
$students = [];

// Fetch students if class and exam type are selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($class_id) && !empty($exam_type) && !empty($subject_id)) {
    $stmt = $db_conn->prepare("SELECT * FROM accounts WHERE type = 'student' AND class_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $students_query = $stmt->get_result();

    if ($students_query->num_rows > 0) {
        $students = mysqli_fetch_all($students_query, MYSQLI_ASSOC);
    } else {
        echo "<div class='alert alert-warning'>No students found for the selected class.</div>";
    }
}

// Fetch existing results for display
$existing_results = [];
if (!empty($class_id) && !empty($exam_type) && !empty($subject_id)) {
    $results_sql = "SELECT student_id, score FROM results WHERE exam_type = ? AND course_id = ?";
    $stmt = $db_conn->prepare($results_sql);
    $stmt->bind_param("si", $exam_type, $subject_id);
    $stmt->execute();
    $results_query = $stmt->get_result();

    while ($row = $results_query->fetch_assoc()) {
        $existing_results[$row['student_id']] = $row['score']; // Store existing results
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Post Results</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Admin</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header py-2">
                        <h3 class="card-title">Post Results</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" class="mb-4">
                            <div class="form-group">
                                <label for="class_id">Select Class:</label>
                                <select name="class_id" id="class_id" class="form-control" required onchange="this.form.submit()">
                                    <option value="">--Select Class--</option>
                                    <?php while ($class = mysqli_fetch_assoc($classes_query)): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $class_id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </form>

                        <?php if (!empty($class_id)): ?>
                            <form action="" method="POST" class="mb-4">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                <div class="form-group">
                                    <label for="exam_type">Select Exam Type:</label>
                                    <select name="exam_type" id="exam_type" class="form-control" required onchange="this.form.submit()">
                                        <option value="">--Select Exam Type--</option>
                                        <option value="FA1" <?php echo $exam_type == 'FA1' ? 'selected' : ''; ?>>FA1</option>
                                        <option value="FA2" <?php echo $exam_type == 'FA2' ? 'selected' : ''; ?>>FA2</option>
                                        <option value="FA3" <?php echo $exam_type == 'FA3' ? 'selected' : ''; ?>>FA3</option>
                                        <option value="FA4" <?php echo $exam_type == 'FA4' ? 'selected' : ''; ?>>FA4</option>
                                        <option value="SA1" <?php echo $exam_type == 'SA1' ? 'selected' : ''; ?>>SA1</option>
                                        <option value="Final" <?php echo $exam_type == 'Final' ? 'selected' : ''; ?>>Final</option>
                                    </select>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if (!empty($exam_type)): ?>
                            <form action="" method="POST" class="mb-4">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
                                <div class="form-group">
                                    <label for="subject_id">Select Subject:</label>
                                    <select name="subject_id" id="subject_id" class="form-control" required onchange="this.form.submit()">
                                        <option value="">--Select Subject--</option>
                                        <?php foreach ($courses_list as $course): ?>
                                            <option value="<?php echo $course['id']; ?>" <?php echo $course['id'] == $subject_id ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        <?php endif; ?>

                        <!-- Displaying Marks Update Form -->
                        <?php if (!empty($subject_id)): ?>
                            <h4 class="mt-4">Enter Marks for Selected Subject</h4>
                            <form action="" method="POST">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
                                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($students)): ?>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                    <td>
                                                        <input type="number" name="results[<?php echo $student['id']; ?>]" class="form-control" value="<?php echo isset($existing_results[$student['id']]) ? htmlspecialchars($existing_results[$student['id']]) : ''; ?>" min="0" max="100" required>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3">No students found.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-primary">Submit Results</button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>
