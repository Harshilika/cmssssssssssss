<?php
include('../includes/config.php');
include('header.php');
include('sidebar.php');

if (!isset($_SESSION['student_id'])) {
    die("You must be logged in as a student to access this page.");
}

$student_id = $_SESSION['student_id'];

// Fetch subjects (courses) for the student
$subjects_query = "
    SELECT DISTINCT c.id, c.name 
    FROM Courses c 
    INNER JOIN Assignments a ON c.id = a.course_id 
    INNER JOIN Classes cl ON a.class_id = cl.id 
    INNER JOIN accounts s ON cl.id = s.class_id 
    WHERE s.id = '$student_id' 
    ORDER BY c.name";
$subjects_result = mysqli_query($db_conn, $subjects_query);

// Handle subject selection
$selected_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;

// Fetch assignments for the selected subject, only if a subject is selected
$assignments_result = [];
if ($selected_subject_id) {
    $assignments_query = "
        SELECT a.id AS assignment_id, a.question 
        FROM Assignments a 
        WHERE a.course_id = '$selected_subject_id' 
        AND a.class_id IN (SELECT class_id FROM accounts WHERE id = '$student_id')";
    $assignments_result = mysqli_query($db_conn, $assignments_query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = (int)$_POST['assignment_id'];
    $answer = mysqli_real_escape_string($db_conn, $_POST['answer']);

    // Validate inputs
    if (empty($answer)) {
        die("Please provide your answer.");
    }

    // Check if the student has already submitted an answer for this assignment
    $check_answer_query = "
        SELECT answer 
        FROM StudentAnswers 
        WHERE assignment_id = '$assignment_id' AND student_id = '$student_id'";
    $check_answer_result = mysqli_query($db_conn, $check_answer_query);
    
    if (mysqli_num_rows($check_answer_result) > 0) {
        die("You have already submitted an answer for this assignment.");
    }

    // Insert answer into the database
    $insert_answer_query = "
        INSERT INTO StudentAnswers (assignment_id, student_id, answer) 
        VALUES ('$assignment_id', '$student_id', '$answer')";
    
    if (mysqli_query($db_conn, $insert_answer_query)) {
        echo "<div class='alert alert-success'>Answer submitted successfully!</div>";
    } else {
        die("Error submitting answer: " . mysqli_error($db_conn));
    }
}
?>

<style>
    /* Add your styling here */
    .assignment {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    .subject-nav {
        margin-bottom: 20px;
    }
    .subject-nav a {
        margin-right: 15px;
    }
</style>

<div class="content-header">
    <h1 class="m-0 text-dark">Assignments</h1>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="subject-nav">
            <h3>Select a Subject:</h3>
            <?php while ($subject = mysqli_fetch_assoc($subjects_result)): ?>
                <a href="?subject_id=<?= $subject['id'] ?>" class="btn btn-secondary"><?= htmlspecialchars($subject['name']) ?></a>
            <?php endwhile; ?>
        </div>

        <?php if ($selected_subject_id && mysqli_num_rows($assignments_result) > 0): ?>
            <div class="card">
                <div class="card-body">
                    <h3>Assignments for 
                    <?php 
                        // Fetch the subject name for display
                        $subject_name_query = "SELECT name FROM Courses WHERE id = '$selected_subject_id'";
                        $subject_name_result = mysqli_query($db_conn, $subject_name_query);
                        $subject_row = mysqli_fetch_assoc($subject_name_result);
                        echo htmlspecialchars($subject_row['name']);
                    ?>
                    </h3>
                    <?php while ($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                        <div class="assignment">
                            <h4>Assignment Question:</h4>
                            <p><?= htmlspecialchars($assignment['question']) ?></p>

                            <?php
                            // Check if the student has already submitted an answer for this assignment
                            $check_answer_query = "
                                SELECT answer 
                                FROM StudentAnswers 
                                WHERE assignment_id = '{$assignment['assignment_id']}' AND student_id = '$student_id'";
                            $check_answer_result = mysqli_query($db_conn, $check_answer_query);
                            $existing_answer = mysqli_fetch_assoc($check_answer_result);
                            ?>

                            <?php if ($existing_answer): ?>
                                <p>Your previous answer: <?= htmlspecialchars($existing_answer['answer']) ?></p>
                                <p class="text-warning">You cannot submit an answer for this assignment again.</p>
                            <?php else: ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">
                                    <label for="answer">Your Answer:</label>
                                    <textarea class="form-control" id="answer" name="answer" required onpaste="return false;"></textarea>
                                    <button type="submit" class="btn btn-primary">Submit Answer</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php elseif ($selected_subject_id): ?>
            <div class="alert alert-info">No assignments available for this subject.</div>
        <?php endif; ?>
    </div>
</section>

<?php include('footer.php'); ?>
