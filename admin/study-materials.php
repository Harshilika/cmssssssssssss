<?php 
include('../includes/config.php'); 
include('header.php'); 
include('sidebar.php'); 

// Fetch all classes
$query_classes = mysqli_query($db_conn, "SELECT id, title FROM classes");
if (!$query_classes) {
    die("Error fetching classes: " . mysqli_error($db_conn));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($db_conn, $_POST['title']);
    $class_id = (int)$_POST['class_id'];
    $file_attachment = $_FILES['file_attachment'];

    // Validate inputs
    if (empty($title) || empty($class_id) || $file_attachment['error'] !== UPLOAD_ERR_OK) {
        die("Please provide valid title, class, and file.");
    }

    // Handle file upload
    $upload_directory = '../dist/uploads/';
    $upload_file = $upload_directory . basename($file_attachment['name']);
    if (move_uploaded_file($file_attachment['tmp_name'], $upload_file)) {
        // Insert post into the posts table
        $insert_post = "INSERT INTO posts (title, type) VALUES ('$title', 'study-material')";
        if (mysqli_query($db_conn, $insert_post)) {
            $post_id = mysqli_insert_id($db_conn); // Get the last inserted post ID
            
            // Insert class metadata
            $insert_metadata = "INSERT INTO metadata (item_id, meta_key, meta_value) VALUES ($post_id, 'class', $class_id)";
            mysqli_query($db_conn, $insert_metadata);

            // Insert file attachment metadata
            $insert_attachment = "INSERT INTO metadata (item_id, meta_key, meta_value) VALUES ($post_id, 'file_attachment', '" . mysqli_real_escape_string($db_conn, $file_attachment['name']) . "')";
            mysqli_query($db_conn, $insert_attachment);
            
            echo "Study material posted successfully!";
        } else {
            die("Error inserting post: " . mysqli_error($db_conn));
        }
    } else {
        die("File upload failed.");
    }
}
?>

<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 text-dark">Add Study Material</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title">Upload Study Material</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="class_id">Select Class</label>
                        <select class="form-control" id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php 
                            if (mysqli_num_rows($query_classes) > 0) {
                                while ($class = mysqli_fetch_assoc($query_classes)) {
                                    ?>
                                    <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['title']); ?></option>
                                    <?php
                                }
                            } else {
                                echo "<!-- No classes found in the database -->";
                            }
                            ?>
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
    </div>
</section>

<?php include('footer.php'); ?>
