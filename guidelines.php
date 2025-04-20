<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidelines - Music Player</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffc107;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 900px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        ul {
            list-style-type: disc;
            margin-top: 1rem;
            padding-left: 1.5rem;
        }

        li {
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Guidelines for Using the Music Player</h1>
        <ul>
    <li>Use your registered username to log in.</li>
    <li>You can update your username anytime from the dashboard.</li>
    <li>Browse available songs and assign your favorites to your profile.</li>
    <li>Click "Let's Hear Some Music" to explore mood-based music options.</li>
    <li>Select your mood and click let's hear some music.</li>
    <li>Or Click Mood based music by capturing your facial expression.</li>
    <div style="text-align:center; margin: 30px 0;">
    <img src="Face-detection-1000x617-1-600x370-1.png" 
         alt="Facial Recognition Process" 
         style="max-width:100%; border-radius:12px; box-shadow: 0 6px 20px rgba(255, 255, 255, 0.25);">
</div>

    <li>Music will play from spotify</li>
    <li>You can remove songs from your profile at any time.</li>
    <li>If you are confused about your mood then you can click the button colour says mood!.</li>
    <li>Colour says mood will say which mood you are in.</li>
    <li>Be respectful of other users and follow community standards.</li>
    <li>If you face any technical issues, please contact support.</li>
</ul>
        <a href="user_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
