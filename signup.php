<?php
session_start();
include 'connect.php';

$message = '';

if (isset($_POST['signUp'])) {
    $username = htmlspecialchars($_POST['username']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert into USERS table
    $insertUser = $conn->prepare("INSERT INTO USERS (UserName, Role) VALUES (?, ?)");
    $insertUser->bind_param("ss", $username, $role);

    if ($insertUser->execute()) {
        $user_id = $conn->insert_id;

        // Insert into the appropriate table based on role
        if ($role === 'Admin') {
            $insertRole = $conn->prepare("INSERT INTO Admin (User_id, Password) VALUES (?, ?)");
        } else {
            $insertRole = $conn->prepare("INSERT INTO User (User_id, Password) VALUES (?, ?)");
        }

        $insertRole->bind_param("is", $user_id, $password);

        if ($insertRole->execute()) {
            $message = "Registration Successful! Redirecting to login...";
            header("Refresh: 2; url=index.php");
        } else {
            $message = "Error: " . $conn->error;
        }
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Mood Based Music Generator</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        :root {
            --primary-brown: #5D4037;
            --light-brown: #8D6E63;
            --accent-gold: #FFD700;
            --cream: #EFEBE9;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-brown), var(--light-brown));
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .signup-container h1 {
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
            color: #fff;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Styled select dropdown */
        .input-group select {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23FFD700' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            cursor: pointer;
        }

        .input-group select:focus {
            background: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 2px var(--accent-gold);
        }

        .input-group select option {
            background-color: var(--primary-brown);
            color: #fff;
            padding: 10px;
        }

        .input-group select option[value="Admin"] {
            background-color:rgb(211, 204, 0); /* Orange color for Admin */
            font-weight: bold;
        }

        .input-group select option[value="User"] {
            background-color:rgb(211, 204, 0); /* Green color for User */
            font-weight: bold;
        }

        .input-group select option:first-child {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--accent-gold);
            color: var(--primary-brown);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: transparent;
            color: var(--accent-gold);
            border: 1px solid var(--accent-gold);
        }

        .links {
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .links p {
            margin: 0.5rem 0;
            color: rgba(255, 255, 255, 0.7);
        }

        .links button {
            background: none;
            border: none;
            color: var(--accent-gold);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .links button:hover {
            text-decoration: underline;
        }

        .message {
            color: var(--accent-gold);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Create Your Account</h1>
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                </select>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <input type="submit" class="btn" value="Sign Up" name="signUp">
        </form>
        <div class="links">
            <p>Already have an account? <button onclick="window.location.href='index.php'">Login</button></p>
        </div>
    </div>
</body>
</html>