<?php
include('../includes/config.php');
include('header.php');
include('sidebar.php');

if (!isset($_SESSION['teacher_id'])) {
    die("You must be logged in as a teacher to access this page.");
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch courses for the teacher
$course_query = "SELECT id, name FROM Courses";
$course_result = mysqli_query($db_conn, $course_query);

// Fetch classes for the teacher
$class_query = "SELECT c.id, c.title FROM Classes c 
                INNER JOIN Teacher_Classes tc ON c.id = tc.class_id 
                WHERE tc.teacher_id = '$teacher_id'";
$class_result = mysqli_query($db_conn, $class_query);

// Handle assignment posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'post_assignment') {
        $question = isset($_POST['question']) ? mysqli_real_escape_string($db_conn, $_POST['question']) : '';
        $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 0;
        $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;

        // Validate inputs
        if (empty($question) || $course_id <= 0 || $class_id <= 0) {
            echo "<div class='alert alert-danger'>Please provide a valid question, select a course, and choose a class.</div>";
        } else {
            // Insert assignment into the database
            $insert_query = "INSERT INTO Assignments (teacher_id, course_id, class_id, question) VALUES ('$teacher_id', '$course_id', '$class_id', '$question')";
            if (mysqli_query($db_conn, $insert_query)) {
                echo "<div class='alert alert-success'>Assignment posted successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Error inserting assignment: " . mysqli_error($db_conn) . "</div>";
            }
        }
    }
}

// Fetch assignments for the teacher
$assignments_query = "SELECT a.id AS assignment_id, a.question, c.name AS course_name, cl.title AS class_title 
                      FROM Assignments a 
                      INNER JOIN Courses c ON a.course_id = c.id 
                      INNER JOIN Classes cl ON a.class_id = cl.id
                      WHERE a.teacher_id = '$teacher_id' 
                      ORDER BY a.id DESC";
$assignments_result = mysqli_query($db_conn, $assignments_query);
?>

<style>
    /* Add your styling here */
    .answer {
        display: none; /* Initially hide answers */
    }
    .table {
        width: 100%;
        margin-top: 15px;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>

<div class="content-header">
    <h1 class="m-0 text-dark">Post Assignment</h1>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="class_id">Select Class</label>
                        <select class="form-control" id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php while ($row = mysqli_fetch_assoc($class_result)): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="course_id">Select Course</label>
                        <select class="form-control" id="course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php while ($row = mysqli_fetch_assoc($course_result)): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="question">Assignment Question</label>
                        <textarea class="form-control" id="question" name="question" required></textarea>
                    </div>
                    <input type="hidden" name="action" value="post_assignment">
                    <button type="submit" class="btn btn-primary">Post Assignment</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2>Student Answers</h2>
                <?php while ($assignment = mysqli_fetch_assoc($assignments_result)): ?>
                    <div class="assignment">
                        <h4><?= htmlspecialchars($assignment['class_title']) ?> - <?= htmlspecialchars($assignment['course_name']) ?> - <?= htmlspecialchars($assignment['question']) ?></h4>
                        <p><strong>Assignment No:</strong> <?= htmlspecialchars($assignment['assignment_id']) ?></p>

                        <?php
                        // Fetch answers for this assignment
                        $answers_query = "SELECT sa.answer, a.name AS student_name, sa.submitted_at 
                                          FROM studentanswers sa 
                                          INNER JOIN accounts a ON sa.student_id = a.id 
                                          WHERE sa.assignment_id = {$assignment['assignment_id']} 
                                          ORDER BY sa.submitted_at DESC";
                        $answers_result = mysqli_query($db_conn, $answers_query);
                        if (mysqli_num_rows($answers_result) > 0): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Answer</th>
                                        <th>Submitted At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($answer = mysqli_fetch_assoc($answers_result)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($answer['student_name']) ?></td>
                                            <td>
                                                <span class="student-name" onclick="toggleAnswer(this)">View Answer</span>
                                                <div class="answer">
                                                    <p><?= nl2br(htmlspecialchars($answer['answer'])) ?></p>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($answer['submitted_at']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No answers submitted yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>

<script>
    function toggleAnswer(element) {
        // Toggle the display of the answer
        const answerDiv = element.nextElementSibling;
        if (answerDiv.style.display === "none" || answerDiv.style.display === "") {
            answerDiv.style.display = "block";
        } else {
            answerDiv.style.display = "none";
        }
    }
</script>

<?php include('footer.php'); ?>
