<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

// Fetch the logged-in user's ID
$username = $_SESSION['username'];
$userQuery = $conn->prepare("SELECT User_id FROM Users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$user_id = $user['User_id'];

// Fetch unique moods from the user's assigned songs
$moodsQuery = "SELECT DISTINCT Music.Mood 
               FROM UserMusic 
               JOIN Music ON UserMusic.Music_id = Music.Music_id 
               WHERE UserMusic.User_id = ?";
$moodsStmt = $conn->prepare($moodsQuery);
$moodsStmt->bind_param("i", $user_id);
$moodsStmt->execute();
$moodsResult = $moodsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Mood - Music Player</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: #6f42c1; /* Purple background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }

        .container {
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px); /* Glassmorphism effect */
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 90%;
            max-width: 800px; /* Increased width to accommodate iframe */
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            color: #fff;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
        }

        .mood-form {
            margin-top: 20px;
        }

        /* Improved Select Dropdown Styling */
        .mood-form select {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
            text-align: center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
            padding-right: 2.5rem;
        }

        .mood-form select:focus {
            background: rgba(255, 255, 255, 0.3);
        }

        select option {
            background: #6f42c1; /* Match the purple background */
            color: #fff;
            text-align: center;
        }

        .mood-form button {
            width: 100%;
            padding: 0.75rem;
            background: #28a745; /* Vibrant button color */
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .mood-form button:hover {
            background: #218838; /* Darker shade on hover */
        }

        .iframe-container {
            margin-top: 2rem;
        }

        .iframe-container iframe {
            width: 100%;
            height: 400px;
            border: none;
            border-radius: 15px;
        }

        /* Error message for iframe */
        .iframe-error {
            background: rgba(255, 0, 0, 0.2);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Your Mood</h1>
        <form class="mood-form" method="post" action="music_player.php">
            <select name="mood" required>
                <option value="" disabled selected>Select your mood</option>
                <?php 
                $moodsResult->data_seek(0); // Reset pointer to re-fetch moods
                while ($mood = $moodsResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($mood['Mood']); ?>">
                        <?php echo htmlspecialchars($mood['Mood']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Let's Hear Some Music</button>
        </form>

        <div class="iframe-container">
            <h2>Or Detect Mood Using Your Camera</h2>
            <iframe src="http://localhost:8501" title="Mood Detection" onerror="showError()"></iframe>
            <div id="iframe-error" class="iframe-error" style="display: none;">
                <p>Unable to connect to mood detection service. Please select your mood manually.</p>
            </div>
        </div>
    </div>

    <script>
        function showError() {
            document.getElementById('iframe-error').style.display = 'block';
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>