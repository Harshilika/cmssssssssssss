<?php
include('../includes/config.php');

// Check if the user is logged in as a parent
if (!isset($_SESSION['parent_id']) || $_SESSION['user_type'] != 'parent') {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get the parent ID from the session
$parent_id = $_SESSION['parent_id'];

// Fetch the child associated with the parent
$child_query = "SELECT child_id FROM Parents WHERE id = '$parent_id'";
$child_result = mysqli_query($db_conn, $child_query);
$child_row = mysqli_fetch_assoc($child_result);
$child_id = $child_row['child_id'] ?? null;

$messages = [];
if ($child_id) {
    // Fetch messages for the child along with course information
    $message_query = "
        SELECT c.id, c.message, c.timestamp, a.name AS teacher_name, cr.name AS course_name 
        FROM Communication c 
        JOIN accounts a ON c.teacher_id = a.id 
        JOIN Courses cr ON c.course_id = cr.id 
        WHERE c.student_id = '$child_id' 
        ORDER BY c.timestamp DESC
    ";
    $message_result = mysqli_query($db_conn, $message_query);
    
    while ($message_row = mysqli_fetch_assoc($message_result)) {
        $messages[] = $message_row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .alert {
            margin-top: 20px;
        }
        .modal-lg {
            max-width: 800px; /* Adjust modal width here */
        }
        .modal-body {
            max-height: 500px; /* Limit height of modal body */
            overflow-y: auto; /* Scroll if content exceeds max height */
            word-wrap: break-word; /* Ensure long words wrap */
            white-space: pre-wrap; /* Ensure that whitespace is preserved */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Messages from Teacher</h1>
        
        <?php if (empty($messages)): ?>
            <p>No messages from your child's teacher.</p>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Course Name</th>
                        <th>Message</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?= htmlspecialchars($msg['teacher_name']) ?></td>
                            <td><?= htmlspecialchars($msg['course_name']) ?></td>
                            <td>
                                <!-- View Message Button -->
                                <button class="btn btn-primary" data-toggle="modal" data-target="#messageModal<?= htmlspecialchars($msg['id']) ?>">View Message</button>

                                <!-- Modal for Viewing Message -->
                                <div class="modal fade" id="messageModal<?= htmlspecialchars($msg['id']) ?>" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document"> <!-- Added modal-lg class -->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="messageModalLabel">Message from <?= htmlspecialchars($msg['teacher_name']) ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p> <!-- Using nl2br to preserve new lines -->
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($msg['timestamp']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
    </div>
</body>
</html>
