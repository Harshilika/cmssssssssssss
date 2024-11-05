<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Ensure the user is logged in as a teacher
if (!isset($_SESSION['teacher_id'])) {
    die("You must be logged in as a teacher to access this page.");
}

// Fetch teacher's classes
$teacher_id = $_SESSION['teacher_id'];
$query_classes = mysqli_query($db_conn, "
    SELECT c.id, c.title 
    FROM classes c 
    JOIN teacher_classes tc ON c.id = tc.class_id 
    WHERE tc.teacher_id = $teacher_id
");

if (!$query_classes) {
    die("Error fetching classes: " . mysqli_error($db_conn));
}

// Fetch courses
$query_courses = mysqli_query($db_conn, "
    SELECT id, name 
    FROM courses
");

if (!$query_courses) {
    die("Error fetching courses: " . mysqli_error($db_conn));
}

// Handle class selection
$selected_class_id = null;
if (isset($_GET['class_id'])) {
    $selected_class_id = (int)$_GET['class_id'];
}

// Handle POST request for uploading study material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($db_conn, $_POST['title']);
    $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : null; 
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null; 

    $file_attachment = $_FILES['file_attachment'];

    // Validate inputs
    if (empty($title) || empty($class_id) || empty($course_id) || $file_attachment['error'] !== UPLOAD_ERR_OK) {
        die("Please provide valid title, class, course, and file.");
    }

    // Handle file upload
    $upload_directory = '../dist/uploads/';
    $upload_file = $upload_directory . basename($file_attachment['name']);
    if (move_uploaded_file($file_attachment['tmp_name'], $upload_file)) {
        // Insert post into the posts table including class_id and course_id
        $insert_post = "INSERT INTO posts (title, type, class_id, course_id) VALUES ('$title', 'study-material', $class_id, $course_id)";
        if (mysqli_query($db_conn, $insert_post)) {
            $post_id = mysqli_insert_id($db_conn); // Get the last inserted post ID
            
            // Insert file attachment metadata
            $insert_attachment = "INSERT INTO metadata (item_id, meta_key, meta_value) VALUES ($post_id, 'file_attachment', '" . mysqli_real_escape_string($db_conn, $file_attachment['name']) . "')";
            mysqli_query($db_conn, $insert_attachment);
            
            echo "<div class='alert alert-success'>Study material posted successfully!</div>";
        } else {
            die("Error inserting post: " . mysqli_error($db_conn));
        }
    } else {
        die("File upload failed.");
    }
}

// Ensure the selected_class_id is valid before fetching materials
$query_materials = null; // Initialize it as null
if ($selected_class_id) {
    $query_materials = mysqli_query($db_conn, "
        SELECT p.id, p.title, m.meta_value AS file_attachment 
        FROM posts p 
        JOIN metadata m ON p.id = m.item_id 
        WHERE p.class_id = $selected_class_id AND m.meta_key = 'file_attachment'
    ");

    // Check for query execution errors
    if ($query_materials === false) {
        die("Error fetching materials: " . mysqli_error($db_conn));
    }
}
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
</style>

<div class="content-header">
    <h1 class="m-0 text-dark">Add Study Material</h1>
</div>

<!-- Navigation bar for class selection -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <?php while ($class = mysqli_fetch_assoc($query_classes)): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($selected_class_id == $class['id']) ? 'active' : ''; ?>" 
                       href="?class_id=<?php echo $class['id']; ?>">
                        <?php echo htmlspecialchars($class['title']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</nav>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Upload Study Material</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    <div class="form-group">
                        <label for="course_id">Select Course</label>
                        <select class="form-control" id="course_id" name="course_id" required>
                            <option value="">Select a course</option>
                            <?php while ($course = mysqli_fetch_assoc($query_courses)): ?>
                                <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="file_attachment">File Attachment</label>
                        <input type="file" class="form-control" id="file_attachment" name="file_attachment" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>

        <!-- Display uploaded materials -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Submitted Study Materials</h3>
            </div>
            <div class="card-body">
                <?php if ($query_materials && mysqli_num_rows($query_materials) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>File Attachment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($material = mysqli_fetch_assoc($query_materials)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($material['title']); ?></td>
                                    <td>
                                        <a href="../dist/uploads/<?php echo htmlspecialchars($material['file_attachment']); ?>" target="_blank">
                                            View File
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No study materials submitted yet for this class.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include('footer.php'); ?>
