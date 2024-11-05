<?php
session_start(); // Start the session
include('../includes/config.php'); // Include your database configuration

if (isset($_POST['login'])) {
    // Retrieve and sanitize user inputs
    $email = mysqli_real_escape_string($db_conn, $_POST['email']);
    $pass = mysqli_real_escape_string($db_conn, $_POST['password']);
    
    // Hash the password using md5
    $pass_md5 = md5($pass);

    // Query to check credentials in the accounts table for students and teachers
    $query = mysqli_query($db_conn, "SELECT * FROM accounts WHERE email = '$email' AND password = '$pass_md5'");

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_object($query);
        
        // Store session variables
        $_SESSION['login'] = true;
        $_SESSION['session_id'] = uniqid();
        
        $user_type = $user->type;
        $_SESSION['user_type'] = $user_type;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;

        // Store additional user information based on user type
        if ($user_type === 'teacher') {
            $_SESSION['teacher_id'] = $user->id;
        } else if ($user_type === 'student') {
            $_SESSION['student_id'] = $user->id;
            $_SESSION['class_id'] = $user->class_id; // Assuming students have class_id
        }

        // Redirect to the appropriate dashboard
        header('Location: ../' . $user_type . '/dashboard.php');
        exit();

    } else {
        // Check credentials in the parents table
        $parent_query = mysqli_query($db_conn, "SELECT * FROM parents WHERE parent_email = '$email' AND password = '$pass_md5'");

        if ($parent_query && mysqli_num_rows($parent_query) > 0) {
            $parent = mysqli_fetch_object($parent_query);
            
            // Store session variables for parent
            $_SESSION['login'] = true;
            $_SESSION['session_id'] = uniqid();
            $_SESSION['user_type'] = 'parent';
            $_SESSION['parent_id'] = $parent->id;
            $_SESSION['child_id'] = $parent->child_id;
            $_SESSION['user_email'] = $parent->parent_email;

            // Redirect to the parent dashboard
            header('Location: ../parent/dashboard.php');
            exit();
        } else if ($email === 'admin@example.com' && $pass === 'admin@sms') {
            $_SESSION['login'] = true;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_email'] = $email;
            header('Location: ../admin/dashboard.php');
            exit();
        } else {
            // Provide feedback for invalid credentials
            echo 'Invalid Credentials'; // Display error message
        }
    }
} else {
    // If not a POST request, you might want to show the login form again
    echo 'Please log in.';
}
?>
