<?php 
include('../includes/config.php'); // Include your database configuration

// Handle form submission for adding a student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === "add") {
        // Adding a new student
        $student_name = mysqli_real_escape_string($db_conn, $_POST['student_name']);
        $student_email = mysqli_real_escape_string($db_conn, $_POST['student_email']);
        $class_id = (int)$_POST['class_id'];
        $parent_name = mysqli_real_escape_string($db_conn, $_POST['parent_name']);
        $parent_phone = mysqli_real_escape_string($db_conn, $_POST['parent_phone']);
        $parent_email = mysqli_real_escape_string($db_conn, $_POST['parent_email']);

        // Set default passwords and hash them
        $student_password = md5("student_password");
        $parent_password = md5("parent_password");

        $insert_student_sql = "INSERT INTO accounts (name, type, email, password, class_id) 
                               VALUES ('$student_name', 'student', '$student_email', '$student_password', $class_id)";
        
        if (mysqli_query($db_conn, $insert_student_sql)) {
            $student_id = mysqli_insert_id($db_conn);
            $insert_parent_sql = "INSERT INTO parents (type, parent_name, parent_email, parent_phone, password, child_id) 
                                  VALUES ('parent', '$parent_name', '$parent_email', '$parent_phone', '$parent_password', $student_id)";
            
            if (mysqli_query($db_conn, $insert_parent_sql)) {
                $success_message = "Student and parent added successfully!";
            } else {
                $error_message = "Error adding parent: " . mysqli_error($db_conn);
            }
        } else {
            $error_message = "Error adding student: " . mysqli_error($db_conn);
        }
    } elseif ($action === "edit") {
        // Editing an existing student and parent
        $student_id = (int)$_POST['student_id'];
        $student_name = mysqli_real_escape_string($db_conn, $_POST['student_name']);
        $student_email = mysqli_real_escape_string($db_conn, $_POST['student_email']);
        $class_id = (int)$_POST['class_id'];
        $parent_name = mysqli_real_escape_string($db_conn, $_POST['parent_name']);
        $parent_phone = mysqli_real_escape_string($db_conn, $_POST['parent_phone']);
        $parent_email = mysqli_real_escape_string($db_conn, $_POST['parent_email']);

        $update_student_sql = "UPDATE accounts SET name='$student_name', email='$student_email', class_id=$class_id 
                               WHERE id=$student_id AND type='student'";
        
        $update_parent_sql = "UPDATE parents SET parent_name='$parent_name', parent_email='$parent_email', parent_phone='$parent_phone' 
                              WHERE child_id=$student_id";
        
        if (mysqli_query($db_conn, $update_student_sql) && mysqli_query($db_conn, $update_parent_sql)) {
            $success_message = "Student and parent details updated successfully!";
        } else {
            $error_message = "Error updating details: " . mysqli_error($db_conn);
        }
    }
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Delete parent first (FK constraint), then delete student
    $delete_parent_sql = "DELETE FROM parents WHERE child_id=$delete_id";
    $delete_student_sql = "DELETE FROM accounts WHERE id=$delete_id AND type='student'";
    
    if (mysqli_query($db_conn, $delete_parent_sql) && mysqli_query($db_conn, $delete_student_sql)) {
        $success_message = "Student and parent deleted successfully!";
    } else {
        $error_message = "Error deleting records: " . mysqli_error($db_conn);
    }
}

// Fetch classes for dropdown
$classes_sql = "SELECT * FROM classes";
$classes_query = mysqli_query($db_conn, $classes_sql);

// Selected class ID for fetching students
$selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Fetch students in selected class
$students_sql = "SELECT a.id AS student_id, a.name AS student_name, a.email AS student_email, 
                        p.parent_name, p.parent_email, p.parent_phone 
                 FROM accounts a 
                 LEFT JOIN parents p ON p.child_id = a.id 
                 WHERE a.type = 'student' AND a.class_id = $selected_class_id 
                 ORDER BY a.name";
$students_query = mysqli_query($db_conn, $students_sql);

// Fetch class title
$class_title_sql = "SELECT title FROM classes WHERE id = $selected_class_id";
$class_title_query = mysqli_query($db_conn, $class_title_sql);
$class_title = mysqli_fetch_assoc($class_title_query)['title'] ?? 'Unknown Class';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Styling goes here */
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Students Management</h1>

        <!-- Add New Student Button -->
        <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addStudentModal">Add New Student</button>

        <!-- Class Selection Dropdown -->
        <form method="GET" action="">
            <select name="class_id" class="form-control" onchange="this.form.submit()">
                <option value="">--Select Class--</option>
                <?php while ($class = mysqli_fetch_assoc($classes_query)): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo $class['id'] == $selected_class_id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Students Table -->
        <?php if ($selected_class_id > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Student Email</th>
                        <th>Parent Name</th>
                        <th>Parent Email</th>
                        <th>Parent Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = mysqli_fetch_assoc($students_query)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['student_email']); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_email']); ?></td>
                            <td><?php echo htmlspecialchars($student['parent_phone']); ?></td>
                            <td>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#editStudentModal" 
                                        onclick="editStudent(<?php echo $student['student_id']; ?>, 
                                                             '<?php echo addslashes($student['student_name']); ?>',
                                                             '<?php echo addslashes($student['student_email']); ?>',
                                                             '<?php echo addslashes($student['parent_name']); ?>',
                                                             '<?php echo addslashes($student['parent_email']); ?>',
                                                             '<?php echo addslashes($student['parent_phone']); ?>')">Edit</button>
                                <a href="?delete_id=<?php echo $student['student_id']; ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Modals for Add and Edit -->
        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Student</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="student_name">Student Name</label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required>
                            </div>
                            <div class="form-group">
                                <label for="student_email">Student Email</label>
                                <input type="email" class="form-control" id="student_email" name="student_email" required>
                            </div>
                            <div class="form-group">
                                <label for="class_id">Class</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">--Select Class--</option>
                                    <?php 
                                    // Reset classes query for modal
                                    $classes_query_modal = mysqli_query($db_conn, $classes_sql);
                                    while ($class = mysqli_fetch_assoc($classes_query_modal)): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['title']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="parent_name">Parent Name</label>
                                <input type="text" class="form-control" id="parent_name" name="parent_name" required>
                            </div>
                            <div class="form-group">
                                <label for="parent_email">Parent Email</label>
                                <input type="email" class="form-control" id="parent_email" name="parent_email" required>
                            </div>
                            <div class="form-group">
                                <label for="parent_phone">Parent Phone</label>
                                <input type="text" class="form-control" id="parent_phone" name="parent_phone" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Student</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="student_id" id="edit_student_id">
                            <div class="form-group">
                                <label for="edit_student_name">Student Name</label>
                                <input type="text" class="form-control" id="edit_student_name" name="student_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_student_email">Student Email</label>
                                <input type="email" class="form-control" id="edit_student_email" name="student_email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_class_id">Class</label>
                                <select class="form-control" id="edit_class_id" name="class_id" required>
                                    <option value="">--Select Class--</option>
                                    <?php 
                                    // Reset classes query for modal
                                    $classes_query_modal = mysqli_query($db_conn, $classes_sql);
                                    while ($class = mysqli_fetch_assoc($classes_query_modal)): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['title']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_parent_name">Parent Name</label>
                                <input type="text" class="form-control" id="edit_parent_name" name="parent_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_parent_email">Parent Email</label>
                                <input type="email" class="form-control" id="edit_parent_email" name="parent_email" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_parent_phone">Parent Phone</label>
                                <input type="text" class="form-control" id="edit_parent_phone" name="parent_phone" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- JavaScript for handling Edit Modal -->
        <script>
            function editStudent(id, studentName, studentEmail, parentName, parentEmail, parentPhone) {
                document.getElementById('edit_student_id').value = id;
                document.getElementById('edit_student_name').value = studentName;
                document.getElementById('edit_student_email').value = studentEmail;
                document.getElementById('edit_parent_name').value = parentName;
                document.getElementById('edit_parent_email').value = parentEmail;
                document.getElementById('edit_parent_phone').value = parentPhone;
            }
        </script>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success mt-3">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger mt-3">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
