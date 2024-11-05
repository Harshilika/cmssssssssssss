<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include('../includes/config.php'); // Include your database connection config

// Function to check if an email already exists
function checkEmailExists($db_conn, $email) {
    $stmt = $db_conn->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Admin submits the student registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input fields
    $name = sanitizeInput($_POST['name'] ?? '');
    $dob = sanitizeInput($_POST['dob'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $father_name = sanitizeInput($_POST['father_name'] ?? '');
    $father_mobile = sanitizeInput($_POST['father_mobile'] ?? '');

    // Check for required fields
    if (empty($name) || empty($dob) || empty($email) || empty($mobile) || empty($father_name) || empty($father_mobile)) {
        echo json_encode(['error' => 'All fields are required.']);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format.']);
        exit;
    }

    // Check if email already exists
    if (checkEmailExists($db_conn, $email)) {
        echo json_encode(['error' => 'Email already exists']);
        exit;
    }

    // Create password from DOB
    $password = password_hash(date('dmY', strtotime($dob)), PASSWORD_DEFAULT);

    // Insert the student account into the `accounts` table
    $stmt = $db_conn->prepare("INSERT INTO accounts (name, email, password, type) VALUES (?, ?, ?, 'student')");
    $stmt->bind_param("sss", $name, $email, $password);

    if (!$stmt->execute()) {
        echo json_encode(['error' => 'Failed to register student']);
        exit;
    }

    // Get the ID of the inserted student account
    $user_id = $db_conn->insert_id;

    // Insert additional metadata in the `usermeta` table
    $metadata = [
        'dob' => $dob,
        'mobile' => $mobile,
        'father_name' => $father_name,
        'father_mobile' => $father_mobile,
        'class' => sanitizeInput($_POST['class'] ?? ''),
        'address' => sanitizeInput($_POST['address'] ?? ''),
        'country' => sanitizeInput($_POST['country'] ?? ''),
        'state' => sanitizeInput($_POST['state'] ?? ''),
        'zip' => sanitizeInput($_POST['zip'] ?? '')
    ];

    $stmt = $db_conn->prepare("INSERT INTO usermeta (user_id, meta_key, meta_value) VALUES (?, ?, ?)");
    foreach ($metadata as $key => $value) {
        $stmt->bind_param("iss", $user_id, $key, $value);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to save user metadata']);
            exit;
        }
    }

    // Parent registration or updating logic
    if (checkEmailExists($db_conn, $father_mobile)) {
        $stmt = $db_conn->prepare("SELECT a.id FROM accounts a WHERE a.type = 'parent' AND a.email = ?");
        $stmt->bind_param("s", $father_mobile);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_object();

        $children = unserialize($result->meta_value) ?: [];
        $children[] = $user_id;
        $children = serialize($children);

        $stmt = $db_conn->prepare("UPDATE usermeta SET meta_value = ? WHERE meta_key = 'children' AND user_id = ?");
        $stmt->bind_param("si", $children, $result->id);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to update parent']);
            exit;
        }
    } else {
        $parent_password = password_hash($father_mobile, PASSWORD_DEFAULT);
        $stmt = $db_conn->prepare("INSERT INTO accounts (name, email, password, type) VALUES (?, ?, ?, 'parent')");
        $stmt->bind_param("sss", $father_name, $father_mobile, $parent_password);

        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to register parent']);
            exit;
        }

        $parent_id = $db_conn->insert_id;
        $children = serialize([$user_id]);
        $stmt = $db_conn->prepare("INSERT INTO usermeta (user_id, meta_key, meta_value) VALUES (?, 'children', ?)");
        $stmt->bind_param("is", $parent_id, $children);
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to save parent-child relationship']);
            exit;
        }
    }

    echo json_encode(['success' => true, 'student_id' => $user_id]);
} else {
    echo json_encode(['error' => 'Invalid request method', 'method' => $_SERVER['REQUEST_METHOD']]);
}
?>
