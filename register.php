<?php
session_start();
include 'connect.php';

// Initialize error message
$error = '';

// Handle registration form submission
if (isset($_POST['signUp'])) {
    $username = htmlspecialchars($_POST['username']);
    $role = htmlspecialchars($_POST['role']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($role) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Check if the username already exists
        $checkUser = $conn->prepare("SELECT User_id FROM USERS WHERE UserName = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $checkUserResult = $checkUser->get_result();

        if ($checkUserResult->num_rows > 0) {
            $error = "Username already taken. Please choose another.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the USERS table
            $insertUser = $conn->prepare("INSERT INTO USERS (UserName, Role) VALUES (?, ?)");
            $insertUser->bind_param("ss", $username, $role);

            if ($insertUser->execute()) {
                $user_id = $insertUser->insert_id; // Get the newly created user's ID

                // Insert the hashed password into the appropriate table based on role
                if ($role === 'Admin') {
                    $insertPassword = $conn->prepare("INSERT INTO Admin (User_id, Password) VALUES (?, ?)");
                } else {
                    $insertPassword = $conn->prepare("INSERT INTO User (User_id, Password) VALUES (?, ?)");
                }

                $insertPassword->bind_param("is", $user_id, $hashedPassword);

                if ($insertPassword->execute()) {
                    // Registration successful
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    // Redirect based on role
                    if ($role === 'Admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Error registering user. Please try again.";
                }
            } else {
                $error = "Error registering user. Please try again.";
            }
        }
    }
}

// If there's an error, redirect back to the signup page with the error message
if (!empty($error)) {
    $_SESSION['error'] = $error;
    header("Location: index.html");
    exit();
}
?>