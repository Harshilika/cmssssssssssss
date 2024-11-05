<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Handle form submission for result recording
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['results'])) {
    $class_id = $_POST['class_id'];
    $exam_type = $_POST['exam_type'];

    // Loop through each course for each student to save their result
    foreach ($_POST['results'] as $student_id => $courses) {
        // Update class_id for students entering their class
        $update_sql = "UPDATE accounts SET class_id = ? WHERE id = ? AND class_id IS NULL";
        $update_stmt = $db_conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $class_id, $student_id);
        $update_stmt->execute();

        foreach ($courses as $course_id => $result) {
            // Insert or update result into the results table
            $insert_sql = "INSERT INTO results (student_id, course_id, exam_type, score) VALUES (?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE score = ?, exam_type = ?";
            $insert_stmt = $db_conn->prepare($insert_sql);
            $insert_stmt->bind_param("iissss", $student_id, $course_id, $exam_type, $result, $result, $exam_type);
            $insert_stmt->execute();
        }
    }

    echo "<div class='alert alert-success'>Results recorded successfully!</div>";
}

// Fetch classes
$classes_sql = "SELECT * FROM classes";
$classes_query = mysqli_query($db_conn, $classes_sql);

// Fetch courses
$courses_sql = "SELECT * FROM courses"; 
$courses_query = mysqli_query($db_conn, $courses_sql);

// Variables to store selected class, course, and exam type
$class_id = $_POST['class_id'] ?? '';
$exam_type = $_POST['exam_type'] ?? ''; 
$students = [];

// Fetch students if class and exam type are selected
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($class_id) && !empty($exam_type) && !isset($_POST['results'])) {
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

// Fetch courses for the selected class
$course_list = [];
if (!empty($class_id)) {
    $course_list_sql = "SELECT * FROM courses";
    $course_list_query = mysqli_query($db_conn, $course_list_sql);
    $course_list = mysqli_fetch_all($course_list_query, MYSQLI_ASSOC);
}

// Fetch existing results for display
$existing_results = [];
if (!empty($class_id) && !empty($exam_type)) {
    $results_sql = "SELECT student_id, score FROM results WHERE exam_type = ?";
    $stmt = $db_conn->prepare($results_sql);
    $stmt->bind_param("s", $exam_type);
    $stmt->execute();
    $results_query = $stmt->get_result();

    while ($row = $results_query->fetch_assoc()) {
        $existing_results[$row['student_id']] = $row['score']; // Store existing results
    }
}

// Fetch total scores and rankings for the selected class and exam type
$ranked_results = [];
if (!empty($class_id) && !empty($exam_type)) {
    $rank_sql = "
        SELECT student_id, SUM(score) as total_score 
        FROM results r
        JOIN accounts a ON r.student_id = a.id
        WHERE a.class_id = ? AND r.exam_type = ?
        GROUP BY r.student_id
        ORDER BY total_score DESC";

    $stmt = $db_conn->prepare($rank_sql);
    $stmt->bind_param("is", $class_id, $exam_type);
    $stmt->execute();
    $rank_results_query = $stmt->get_result();

    // Calculate ranks based on total score, allowing for ties
    $rank = 1;
    $last_score = null; // To keep track of the last total score
    $last_rank = 0; // To assign the last rank for students with the same score

    while ($row = $rank_results_query->fetch_assoc()) {
        if ($last_score === null || $row['total_score'] != $last_score) {
            $last_rank = $rank; // Update the last rank when the score changes
        }

        $ranked_results[$row['student_id']] = [
            'total_score' => $row['total_score'],
            'rank' => $last_rank
        ];

        $last_score = $row['total_score']; // Update last_score to the current score
        $rank++;
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

<!-- Navigation Bar for Exam Types -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Exam Types</a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <form action="" method="POST" class="form-inline">
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <select name="exam_type" class="form-control mr-sm-2" required onchange="this.form.submit()">
                        <option value="">--Select Exam Type--</option>
                        <option value="FA1" <?php echo $exam_type == 'FA1' ? 'selected' : ''; ?>>FA1</option>
                        <option value="FA2" <?php echo $exam_type == 'FA2' ? 'selected' : ''; ?>>FA2</option>
                        <option value="FA3" <?php echo $exam_type == 'FA3' ? 'selected' : ''; ?>>FA3</option>
                        <option value="FA4" <?php echo $exam_type == 'FA4' ? 'selected' : ''; ?>>FA4</option>
                        <option value="SA1" <?php echo $exam_type == 'SA1' ? 'selected' : ''; ?>>SA1</option>
                        <option value="SA2" <?php echo $exam_type == 'SA2' ? 'selected' : ''; ?>>SA2</option>
                    </select>
                </form>
            </li>
        </ul>
    </div>
</nav>

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

                        <?php if (!empty($class_id) && !empty($exam_type)): ?>
                            <div class="mt-4">
                                <form action="" method="POST" class="mb-4">
                                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                    <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
                                    <button type="submit" name="update_results" class="btn btn-primary">Update Marks</button>
                                    <button type="submit" name="view_results" class="btn btn-secondary">View Marks and Ranks</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Displaying Marks Update Form -->
                        <?php if (isset($_POST['update_results'])): ?>
                            <form action="" method="POST">
                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                <input type="hidden" name="exam_type" value="<?php echo $exam_type; ?>">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <?php foreach ($course_list as $course): ?>
                                                <th><?php echo htmlspecialchars($course['name']); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td> <!-- Displaying Student Name -->
                                                <?php foreach ($course_list as $course): ?>
                                                    <td>
                                                        <input type="number" name="results[<?php echo $student['id']; ?>][<?php echo $course['id']; ?>]" value="<?php echo $existing_results[$student['id']] ?? ''; ?>" class="form-control">
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-success mt-3">Submit Results</button>
                            </form>
                        <?php endif; ?>

                        <!-- Display ranked results -->
                        <?php if (isset($_POST['view_results'])): ?>
                            <h4 class="mt-5">Ranked Results:</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th> <!-- Added Student Name Column -->
                                        <th>Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ranked_results as $student_id => $result): ?>
                                        <tr>
                                            <td><?php echo $result['rank']; ?></td>
                                            <td><?php echo htmlspecialchars($student_id); ?></td>
                                            <td>
                                                <?php 
                                                    // Fetch the student's name for display
                                                    $name_sql = "SELECT name FROM accounts WHERE id = ?";
                                                    $name_stmt = $db_conn->prepare($name_sql);
                                                    $name_stmt->bind_param("i", $student_id);
                                                    $name_stmt->execute();
                                                    $name_query = $name_stmt->get_result();
                                                    $student_name = $name_query->fetch_assoc()['name'] ?? 'N/A';
                                                    echo htmlspecialchars($student_name); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($result['total_score']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>
