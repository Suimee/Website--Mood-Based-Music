<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

// Fetch user's music
$username = $_SESSION['username'];
$userQuery = "SELECT User_id FROM USERS WHERE UserName = '$username'";
$userResult = $conn->query($userQuery);
$userRow = $userResult->fetch_assoc();
$user_id = $userRow['User_id'];

// Fetch music associated with the user
$musicQuery = "SELECT Music.Music_id, Music.Title, Music.Artist, Music.Mood 
               FROM Music 
               JOIN UserMusic ON Music.Music_id = UserMusic.Music_id 
               WHERE UserMusic.User_id = $user_id";
$musicResult = $conn->query($musicQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .music-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        .music-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            width: calc(33.333% - 20px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .music-card h3 {
            margin: 0 0 10px;
            color: #007bff;
        }
        .music-card p {
            margin: 5px 0;
            color: #555;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="music-list">
            <?php if ($musicResult->num_rows > 0): ?>
                <?php while ($music = $musicResult->fetch_assoc()): ?>
                    <div class="music-card">
                        <h3><?php echo htmlspecialchars($music['Title']); ?></h3>
                        <p><strong>Artist:</strong> <?php echo htmlspecialchars($music['Artist']); ?></p>
                        <p><strong>Mood:</strong> <?php echo htmlspecialchars($music['Mood']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No music associated with your account.</p>
            <?php endif; ?>
        </div>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>