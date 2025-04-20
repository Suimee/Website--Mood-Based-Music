<?php
session_start();
include 'connect.php';

$error = '';

if (isset($_POST['login'])) {
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    $userQuery = $conn->prepare("SELECT * FROM USERS WHERE UserName = ?");
    $userQuery->bind_param("s", $username);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['User_id'];
        $role = $userRow['Role'];

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
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

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
    <title>Mood-Based Music Generator</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-brown: #5D4037;
            --light-brown: #8D6E63;
            --accent-gold: #FFD700;
            --cream: #EFEBE9;
            --dark-text: #3E2723;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--cream);
            color: var(--dark-text);
            overflow-x: hidden;
        }

        /* Navigation Bar */
        nav {
            background-color: var(--primary-brown);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            z-index: 1000;
        }

        .logo {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            color: var(--accent-gold);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--accent-gold);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--accent-gold);
            bottom: -5px;
            left: 0;
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Main Header */
        .main-header {
            text-align: center;
            padding: 120px 0 40px 0;
            background: linear-gradient(135deg, var(--primary-brown) 0%, var(--light-brown) 100%);
            color: white;
            margin-bottom: 40px;
        }

        .main-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease-out;
        }

        .main-header p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        /* Login Container */
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
            margin: 0 auto 100px auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-brown);
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
            color: rgba(110, 99, 11, 0.37);
        }

        .input-group input:focus {
            background: rgba(245, 235, 123, 0.31);
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
            color: var(--primary-brown);
        }

        .links a {
            color: var(--accent-gold);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #ff6f61;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        /* About Section */
        .about {
            padding: 5rem 5%;
            background-color: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: var(--primary-brown);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: var(--accent-gold);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .about-content {
            display: flex;
            align-items: center;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .about-text {
            flex: 1;
        }

        .about-text p {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .about-image {
            flex: 1;
            position: relative;
            animation: float 6s ease-in-out infinite;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        /* Contact Section */
        .contact {
            padding: 5rem 5%;
            background-color: var(--cream);
        }

        .contact-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }

        .contact-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .contact-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            width: 200px;
        }

        .contact-card:hover {
            transform: translateY(-10px);
        }

        .contact-card i {
            font-size: 2rem;
            color: var(--primary-brown);
            margin-bottom: 1rem;
        }

        /* Footer */
        footer {
            background-color: var(--primary-brown);
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .social-links {
            margin: 1rem 0;
        }

        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--accent-gold);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .about-content {
                flex-direction: column;
            }

            .nav-links {
                gap: 1rem;
            }
            
            .main-header h1 {
                font-size: 2.2rem;
            }
            
            .main-header p {
                font-size: 1rem;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-music"></i>
            Mood Based Music Generator
        </div>
        <div class="nav-links">
            <a href="#" id="login-btn">Login</a>
            <a href="#" id="about-btn">About</a>
            <a href="#" id="contact-btn">Contact Us</a>
        </div>
    </nav>

    <!-- Main Header -->
    <section class="main-header">
        <h1>Mood Based Music Generator</h1>
        <p>Discover personalized music that matches your emotions and enhances your mood</p>
    </section>

    <!-- Login Container -->
    <div class="login-container">
        <h2>Login to Your Account</h2>
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
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

    <!-- About Section -->
    <section class="about" id="about">
        <h2 class="section-title">About Mood Based Music Generator</h2>
        <div class="about-content">
            <div class="about-text">
                <p>Mood Based Music Generator is an innovative music generation platform that creates personalized soundtracks based on your emotional state. Our advanced system analyzes your mood through various inputs and generates music that resonates with how you feel.</p>
                <p>Using face recognition technology and Spotify integration, we suggest original music in real-time that adapts to your preferences and emotional needs. Whether you need focus music for work, calming melodies for relaxation, or upbeat tracks for your workout, Mood Based Music Generator delivers.</p>
                <p>Our technology combines elements from classical composition theory with modern techniques to create music that's both emotionally resonant and musically sophisticated.</p>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Music and emotions">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <h2 class="section-title">Contact Us</h2>
        <div class="contact-content">
            <p>Have questions about Mood Based Music Generator? Our team is here to help you!</p>
            <div class="contact-info">
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <p>01882465305</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <p>01921219519</p>
                </div>
                <div class="contact-card">
                    <i class="fas fa-phone"></i>
                    <p>01821921011</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-spotify"></i></a>
        </div>
        <p>&copy; 2023 Mood Based Music Generator. All rights reserved.</p>
    </footer>

    <!-- JavaScript -->
    <script>
        // Scroll to about section
        document.getElementById('about-btn').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('about').scrollIntoView({ behavior: 'smooth' });
        });

        // Scroll to contact section
        document.getElementById('contact-btn').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
        });

        // Scroll to login section
        document.getElementById('login-btn').addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelector('.login-container').scrollIntoView({ behavior: 'smooth' });
        });
    </script>
</body>
</html>