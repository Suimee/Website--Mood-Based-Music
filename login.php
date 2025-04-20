<?php
session_start();
include 'connect.php';

// Initialize error message
$error = '';

// Handle login form submission
if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // Fetch user from USERS table
    $userQuery = $conn->prepare("SELECT * FROM USERS WHERE UserName = ?");
    $userQuery->bind_param("s", $username);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['User_id'];
        $role = $userRow['Role'];

        // Fetch password from the appropriate table
        if ($role === 'Admin') {
            $roleQuery = $conn->prepare("SELECT * FROM Admin WHERE User_id = ?");
        } else {
            $roleQuery = $conn->prepare("SELECT * FROM User WHERE User_id = ?");
        }

        $roleQuery->bind_param("i", $user_id);
        $roleQuery->execute();
        $roleResult = $roleQuery->get_result();

        if ($roleResult->num_rows > 0) {
            $roleRow = $roleResult->fetch_assoc();
            if (password_verify($password, $roleRow['Password'])) {
                // Login successful
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
                $error = "Incorrect password!";
            }
        } else {
            $error = "User not found in role table!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mood Based Music Generator</title>
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

        .login-container {
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

        .login-container h1 {
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
            color: #fff;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
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

        .error-message {
            color: #ff6f61;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login to Mood Based Music Generator</h1>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <input type="submit" class="btn" value="Login" name="login">
        </form>
        <div class="links">
            <p>Don't have an account? <button id="showSignUp">Sign Up</button></p>
        </div>
    </div>

    <script>
        document.getElementById('showSignUp').addEventListener('click', function () {
            window.location.href = 'signup.php';
        });
    </script>
</body>
</html>