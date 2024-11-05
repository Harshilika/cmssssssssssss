<?php 
session_start();
include('../includes/config.php'); 

// Check if user is logged in as student or parent
if (!isset($_SESSION['student_id']) && !isset($_SESSION['parent_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$student_id = null;
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id']; // Get student ID from session
} elseif (isset($_SESSION['parent_id'])) {
    // If user is a parent, fetch the child ID
    $parent_id = $_SESSION['parent_id'];
    $child_sql = "SELECT child_id FROM parents WHERE id = ?";
    $stmt = $db_conn->prepare($child_sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $stmt->bind_result($student_id);
    $stmt->fetch();
    $stmt->close();
}

$exam_type = $_GET['exam_type'] ?? ''; // Get selected exam type from query parameter
$total_score = 0; // Initialize total score
$rank = 0; // Initialize rank

// Fetch student's results based on selected exam type for all subjects
if ($student_id && $exam_type) {
    $results_sql = "SELECT r.course_id, r.score, c.name FROM results r JOIN courses c ON r.course_id = c.id WHERE r.student_id = ? AND r.exam_type = ?";
    $stmt = $db_conn->prepare($results_sql);
    $stmt->bind_param("is", $student_id, $exam_type);
    $stmt->execute();
    $results_query = $stmt->get_result();

    $results = [];
    while ($row = $results_query->fetch_assoc()) {
        $results[] = $row; // Store results in an array
        $total_score += $row['score']; // Sum the scores
    }

    // Get total scores of all students for the same exam type
    $total_scores_sql = "SELECT student_id, SUM(score) as total_score FROM results WHERE exam_type = ? GROUP BY student_id ORDER BY total_score DESC";
    $total_scores_stmt = $db_conn->prepare($total_scores_sql);
    $total_scores_stmt->bind_param("s", $exam_type);
    $total_scores_stmt->execute();
    $total_scores_query = $total_scores_stmt->get_result();

    $rank = 1; // Initialize rank
    while ($row = $total_scores_query->fetch_assoc()) {
        if ($row['student_id'] == $student_id) {
            // Rank is determined by the number of students with higher total scores
            break;
        }
        $rank++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
            border-radius: 10px;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #343a40;
            text-align: center;
        }
        table {
            margin-top: 20px;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        footer {
            margin-top: 30px;
            text-align: center;
            color: #6c757d;
        }
        nav {
            margin-bottom: 20px;
        }
        .nav-link {
            font-weight: bold;
            color: #007bff !important;
        }
        .nav-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Exams</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=FA1">FA1</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=FA2">FA2</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=FA3">FA3</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=FA4">FA4</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=SA1">SA1</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="?exam_type=SA2">SA2</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Your Child Results for <?php echo htmlspecialchars($exam_type); ?></h2>

        <?php if ($exam_type && !empty($results)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['name']); ?></td>
                            <td><?php echo htmlspecialchars($result['score']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4 class="text-center">Total Score: <strong><?php echo $total_score; ?></strong></h4>
            <h4 class="text-center">Your Rank: <strong><?php echo $rank; ?></strong></h4>
        <?php elseif ($exam_type): ?>
            <p class="text-center">No results found for the selected exam type.</p>
        <?php else: ?>
            <p class="text-center">Please select an exam type to view results.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Your School Name. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
