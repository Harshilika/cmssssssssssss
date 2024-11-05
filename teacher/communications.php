<?php
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Start the session only if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in as a teacher
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in as a teacher to access this page.");
}

// Check if the class is selected
$class_id = $_GET['class_id'] ?? null;

// If a class is selected, fetch students for that class
$students = [];
if ($class_id) {
    // Fetch students and their parent's name using a LEFT JOIN
    $query = "
        SELECT s.*, p.parent_name, p.parent_email, p.parent_phone 
        FROM accounts s 
        LEFT JOIN parents p ON s.id = p.child_id 
        WHERE s.class_id = '$class_id' AND s.type = 'student'
    ";
    $result = mysqli_query($db_conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}

// Handle form submission for messaging parents
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data safely
    $student_id = $_POST['student_id'] ?? null;
    $message = $_POST['message'] ?? null;

    // Insert into the database if values are provided
    if ($student_id && $message) {
        $teacher_id = $_SESSION['user_id']; // Assuming user_id is stored in session

        // Get parent ID for the selected student
        $parent_query = "SELECT id FROM parents WHERE child_id = '$student_id'";
        $parent_result = mysqli_query($db_conn, $parent_query);
        $parent_row = mysqli_fetch_assoc($parent_result);
        $parent_id = $parent_row['id'] ?? null;

        if ($parent_id) {
            $insert_query = "INSERT INTO Communication (teacher_id, student_id, parent_id, message) VALUES ('$teacher_id', '$student_id', '$parent_id', '$message')";
            
            if (mysqli_query($db_conn, $insert_query)) {
                echo "<div class='alert alert-success'>Message sent successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Error: " . mysqli_error($db_conn) . "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Parent not found for the selected student.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Please fill in all fields.</div>";
    }
}

// Fetch classes for the teacher
$teacher_id = $_SESSION['user_id'];
$class_query = "SELECT DISTINCT c.id, c.title FROM Classes c INNER JOIN Teacher_Classes tc ON c.id = tc.class_id WHERE tc.teacher_id = '$teacher_id'";
$class_result = mysqli_query($db_conn, $class_query);
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        padding: 20px;
    }
    .content-header {
        margin-bottom: 20px;
    }
    .card {
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 20px 0;
    }
    .card-header {
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 15px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 10px;
        width: 100%;
        box-sizing: border-box;
    }
    .btn-primary {
        background-color: #007bff;
        border: none;
        color: #ffffff;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .btn-primary:hover {
        background-color: #0056b3;
    }
    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-top: 10px;
        display: inline-block;
    }
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    .nav-pills .nav-link {
        margin-right: 10px;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: #fff;
    }
</style>

<div class="content-header">
    <h1 class="m-0 text-dark">Communicate with Parents</h1>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Select Class</h3>
            </div>
            <div class="card-body">
                <nav>
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link <?= !$class_id ? 'active' : '' ?>" href="communications.php">All Classes</a>
                        </li>
                        <?php while ($row = mysqli_fetch_assoc($class_result)): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($row['id'] == $class_id) ? 'active' : '' ?>" href="communications.php?class_id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </nav>

                <?php if ($class_id): ?>
                    <h2>Students in Class</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Parent Name</th>
                                <th>Parent Email</th>
                                <th>Parent Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['id']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['parent_name']) ?></td>
                                    <td><?= htmlspecialchars($student['parent_email']) ?></td>
                                    <td><?= htmlspecialchars($student['parent_phone']) ?></td>
                                    <td>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#messageModal" data-student-id="<?= $student['id'] ?>" data-student-name="<?= htmlspecialchars($student['name']) ?>">Send Message</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Modal for Sending Message -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="communications.php">
                    <input type="hidden" name="student_id" id="student_id" value="">
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea name="message" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#messageModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var studentId = button.data('student-id'); // Extract info from data-* attributes
        var studentName = button.data('student-name'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('#student_id').val(studentId);
        modal.find('.modal-title').text('Send Message to ' + studentName);
    });
</script>

<?php
include('footer.php'); // Include footer file
?>
