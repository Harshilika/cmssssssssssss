<?php 
include('../includes/config.php'); 

$teacher_added = false; 
$teacher_info = []; 

// Handle form submission for adding a teacher
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_teacher'])) {
    $teacher_id = uniqid('teacher_'); 
    $name = mysqli_real_escape_string($db_conn, $_POST['name']);
    $email = mysqli_real_escape_string($db_conn, $_POST['email']);
    $phone = mysqli_real_escape_string($db_conn, $_POST['phone']); 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $class_ids = $_POST['class_ids'];

    $insert_sql = "INSERT INTO accounts (name, type, email, password, phone) VALUES ('$name', 'teacher', '$email', '$password', '$phone')";
    
    if (mysqli_query($db_conn, $insert_sql)) {
        $teacher_id = mysqli_insert_id($db_conn);
        foreach ($class_ids as $class_id) {
            $class_insert_sql = "INSERT INTO teacher_classes (teacher_id, class_id) VALUES ('$teacher_id', $class_id)";
            mysqli_query($db_conn, $class_insert_sql);
        }

        $teacher_added = true;
        $teacher_info = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'teacher_id' => $teacher_id,
            'class_ids' => $class_ids
        ];
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($db_conn) . "</div>";
    }
}

// Handle deleting a teacher
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $delete_teacher_sql = "DELETE FROM accounts WHERE id = '$delete_id' AND type = 'teacher'";
    $delete_classes_sql = "DELETE FROM teacher_classes WHERE teacher_id = '$delete_id'";

    mysqli_query($db_conn, $delete_classes_sql);
    if (mysqli_query($db_conn, $delete_teacher_sql)) {
        echo "<div class='alert alert-success'>Teacher deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($db_conn) . "</div>";
    }
}

// Handle updating a teacher
if (isset($_POST['update_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    $name = mysqli_real_escape_string($db_conn, $_POST['name']);
    $email = mysqli_real_escape_string($db_conn, $_POST['email']);
    $phone = mysqli_real_escape_string($db_conn, $_POST['phone']);
    $class_ids = $_POST['class_ids'];

    $update_sql = "UPDATE accounts SET name='$name', email='$email', phone='$phone' WHERE id='$teacher_id'";
    mysqli_query($db_conn, $update_sql);

    // Update teacher_classes table
    mysqli_query($db_conn, "DELETE FROM teacher_classes WHERE teacher_id='$teacher_id'");
    foreach ($class_ids as $class_id) {
        mysqli_query($db_conn, "INSERT INTO teacher_classes (teacher_id, class_id) VALUES ('$teacher_id', $class_id)");
    }

    echo "<div class='alert alert-success'>Teacher updated successfully!</div>";
}

// Fetch all teachers for display
$teachers_sql = "SELECT * FROM accounts WHERE type = 'teacher'";
$teachers_query = mysqli_query($db_conn, $teachers_sql);

// Fetch classes to populate the dropdown for adding a teacher
$classes_sql = "SELECT * FROM classes";
$classes_query = mysqli_query($db_conn, $classes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">All Teachers</h1>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Classes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($teacher = mysqli_fetch_assoc($teachers_query)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($teacher['id']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                        <td>
                            <?php 
                            $teacher_classes_sql = "SELECT c.title FROM teacher_classes tc JOIN classes c ON tc.class_id = c.id WHERE tc.teacher_id = " . $teacher['id'];
                            $teacher_classes_query = mysqli_query($db_conn, $teacher_classes_sql);
                            while ($class = mysqli_fetch_assoc($teacher_classes_query)) {
                                echo htmlspecialchars($class['title']) . "<br>";
                            }
                            ?>
                        </td>
                        <td>
                            <a href="?edit_id=<?php echo $teacher['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="?delete_id=<?php echo $teacher['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button class="btn btn-success mt-4" data-toggle="modal" data-target="#addTeacherModal">Add Teacher</button>
        
        <!-- Add Teacher Modal -->
        <div class="modal fade" id="addTeacherModal" tabindex="-1" role="dialog" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTeacherModalLabel">Add Teacher Profile</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="add_teacher" value="1">
                            <div class="form-group">
                                <label for="name">Teacher Name:</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number:</label>
                                <input type="text" name="phone" id="phone" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="class_ids">Select Classes:</label>
                                <select name="class_ids[]" id="class_ids" class="form-control" multiple required>
                                    <?php while ($class = mysqli_fetch_assoc($classes_query)): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['title']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <small>Select multiple classes using Ctrl or Command key.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Teacher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['edit_id'])): ?>
            <?php
            $edit_id = $_GET['edit_id'];
            $teacher_sql = "SELECT * FROM accounts WHERE id = '$edit_id' AND type = 'teacher'";
            $teacher_result = mysqli_query($db_conn, $teacher_sql);
            $teacher_data = mysqli_fetch_assoc($teacher_result);

            $teacher_classes_sql = "SELECT class_id FROM teacher_classes WHERE teacher_id = '$edit_id'";
            $teacher_classes_result = mysqli_query($db_conn, $teacher_classes_sql);
            $selected_classes = array_column(mysqli_fetch_all($teacher_classes_result, MYSQLI_ASSOC), 'class_id');
            ?>
            <div class="mt-4">
                <h2>Edit Teacher Profile</h2>
                <form action="" method="POST">
                    <input type="hidden" name="teacher_id" value="<?php echo $edit_id; ?>">
                    <input type="hidden" name="update_teacher" value="1">
                    <div class="form-group">
                        <label for="name">Teacher Name:</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($teacher_data['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($teacher_data['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($teacher_data['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="class_ids">Select Classes:</label>
                        <select name="class_ids[]" id="class_ids" class="form-control" multiple required>
                            <?php 
                            $classes_query = mysqli_query($db_conn, $classes_sql);
                            while ($class = mysqli_fetch_assoc($classes_query)): 
                                $selected = in_array($class['id'], $selected_classes) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($class['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Teacher</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
