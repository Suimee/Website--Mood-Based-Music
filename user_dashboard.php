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

// Handle Update Username
if (isset($_POST['update_username'])) {
    $new_username = htmlspecialchars($_POST['username']);

    // Check if the new username is already taken
    $checkUsername = $conn->prepare("SELECT User_id FROM Users WHERE username = ?");
    $checkUsername->bind_param("s", $new_username);
    $checkUsername->execute();
    $checkUsernameResult = $checkUsername->get_result();

    if ($checkUsernameResult->num_rows > 0) {
        echo "<p class='error'>Username already taken. Please choose another.</p>";
    } else {
        // Update the username
        $updateUsername = $conn->prepare("UPDATE Users SET username = ? WHERE User_id = ?");
        $updateUsername->bind_param("si", $new_username, $user_id);

        if ($updateUsername->execute()) {
            $_SESSION['username'] = $new_username; // Update session username
            echo "<p class='success'>Username updated successfully!</p>";
        } else {
            echo "<p class='error'>Error updating username: " . $conn->error . "</p>";
        }
    }
}

// Handle Assign Song to Self
if (isset($_POST['assign_song'])) {
    $music_id = $_POST['music_id'];

    // Check if the song is already assigned to the user
    $checkAssignment = $conn->prepare("SELECT * FROM UserMusic WHERE User_id = ? AND Music_id = ?");
    $checkAssignment->bind_param("ii", $user_id, $music_id);
    $checkAssignment->execute();
    $checkAssignmentResult = $checkAssignment->get_result();

    if ($checkAssignmentResult->num_rows > 0) {
        echo "<p class='error'>This song is already assigned to you.</p>";
    } else {
        // Assign the song to the user
        $assignSong = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id) VALUES (?, ?)");
        $assignSong->bind_param("ii", $user_id, $music_id);

        if ($assignSong->execute()) {
            echo "<p class='success'>Song assigned successfully!</p>";
        } else {
            echo "<p class='error'>Error assigning song: " . $conn->error . "</p>";
        }
    }
}

// Handle Remove Song
if (isset($_GET['remove_song'])) {
    $music_id = $_GET['remove_song'];

    // Remove the song from the UserMusic table
    $deleteUserMusic = $conn->prepare("DELETE FROM UserMusic WHERE User_id = ? AND Music_id = ?");
    $deleteUserMusic->bind_param("ii", $user_id, $music_id);

    if ($deleteUserMusic->execute()) {
        // Redirect to the same page to reflect the changes
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "<p class='error'>Error removing song: " . $conn->error . "</p>";
    }
}

// Fetch all songs
$musicQuery = "SELECT Music_id, Title, Artist, Mood FROM Music";
$musicResult = $conn->query($musicQuery);

// Fetch songs assigned to the logged-in user
$userMusicQuery = "SELECT Music.Music_id, Music.Title, Music.Artist, Music.Mood 
                   FROM UserMusic 
                   JOIN Music ON UserMusic.Music_id = Music.Music_id 
                   WHERE UserMusic.User_id = ?";
$userMusicStmt = $conn->prepare($userMusicQuery);
$userMusicStmt->bind_param("i", $user_id);
$userMusicStmt->execute();
$userMusicResult = $userMusicStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Music Player</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffc107; /* Yellow background */
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent black container */
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 1200px;
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
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
            font-weight: 600;
        }

        a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .form-container {
            margin-top: 20px;
        }

        .form-container input, .form-container select {
            width: 100%;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease;
        }

        .form-container input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-container input:focus, .form-container select:focus {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Improved Select Dropdown Styling */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1em;
            padding-right: 2.5rem; /* Make room for the arrow */
        }

        select option {
            background: #333; /* Dark background for options */
            color: #fff;
        }

        .form-container button {
            width: 100%;
            padding: 0.75rem;
            background: #000; /* Black button */
            color: white;
            border: none;
            border-radius: 25px; /* Round buttons */
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Button shadow */
        }

        .form-container button:hover {
            background: #333; /* Darker shade on hover */
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .music-button, .guidelines-button {
            display: block;
            width: 200px;
            padding: 10px;
            color: white;
            text-align: center;
            border-radius: 25px; 
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); 
        }

        .music-button {
            background: #000; 
        }

        .guidelines-button {
            background:rgb(3, 13, 5); 
        }

        .music-button:hover {
            background: #333; 
        }

        .guidelines-button:hover {
            background:rgb(3, 16, 6); 
        }

        .success {
            color:rgb(4, 13, 6);
            background-color: rgba(40, 167, 69, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <a href="logout.php">Logout</a>

        <h2>Update Your Username</h2>
        <div class="form-container">
            <form method="post" action="">
                <input type="text" name="username" placeholder="New Username" required>
                <button type="submit" name="update_username">Update Username</button>
            </form>
        </div>

        <h2>Assign Songs to Yourself</h2>
        <div class="form-container">
            <form method="post" action="">
                <select name="music_id" required>
                    <option value="">Select a song</option>
                    <?php 
                    $musicResult->data_seek(0); // Reset pointer to re-fetch music
                    while ($music = $musicResult->fetch_assoc()): ?>
                        <option value="<?php echo $music['Music_id']; ?>">
                            <?php echo htmlspecialchars($music['Title']); ?> by <?php echo htmlspecialchars($music['Artist']); ?> (<?php echo htmlspecialchars($music['Mood']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="assign_song">Assign Song</button>
            </form>
        </div>

        <h2>Your Songs</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Mood</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($song = $userMusicResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($song['Title']); ?></td>
                        <td><?php echo htmlspecialchars($song['Artist']); ?></td>
                        <td><?php echo htmlspecialchars($song['Mood']); ?></td>
                        <td>
                            <a href="?remove_song=<?php echo $song['Music_id']; ?>" onclick="return confirm('Are you sure you want to remove this song?');">Remove</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Button Container -->
        <div class="button-container">
            <a href="color_mood.php" class="music-button" style="background:rgb(3, 9, 15);">Colour Says Mood</a>
            <a href="select_mood.php" class="music-button">Let's Hear Some Music</a>
            <a href="guidelines.php" class="guidelines-button">Guidelines</a>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>