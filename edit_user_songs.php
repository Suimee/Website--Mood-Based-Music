<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

// Fetch user details
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $userQuery = $conn->prepare("SELECT UserName, Role FROM USERS WHERE User_id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $user = $userResult->fetch_assoc();

    if (!$user) {
        echo "<p>User not found!</p>";
        exit();
    }
} else {
    echo "<p>No user selected!</p>";
    exit();
}

// Fetch all songs
$musicQuery = "SELECT Music_id, Title, Artist FROM Music";
$musicResult = $conn->query($musicQuery);

// Fetch songs assigned to the user
$userMusicQuery = "SELECT Music.Music_id, Music.Title, Music.Artist 
                   FROM UserMusic 
                   JOIN Music ON UserMusic.Music_id = Music.Music_id 
                   WHERE UserMusic.User_id = ?";
$userMusicStmt = $conn->prepare($userMusicQuery);
$userMusicStmt->bind_param("i", $user_id);
$userMusicStmt->execute();
$userMusicResult = $userMusicStmt->get_result();

// Handle Assign Song to User
if (isset($_POST['assign_song'])) {
    $music_id = $_POST['music_id'];

    $assignSong = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id) VALUES (?, ?)");
    $assignSong->bind_param("ii", $user_id, $music_id);

    if ($assignSong->execute()) {
        // Refresh the page to show the updated list
        header("Location: edit_user_songs.php?user_id=$user_id");
        exit();
    } else {
        echo "<p>Error assigning song: " . $conn->error . "</p>";
    }
}

// Handle Remove Song from User
if (isset($_GET['remove_song'])) {
    $music_id = $_GET['remove_song'];

    $removeSong = $conn->prepare("DELETE FROM UserMusic WHERE User_id = ? AND Music_id = ?");
    $removeSong->bind_param("ii", $user_id, $music_id);

    if ($removeSong->execute()) {
        // Refresh the page to show the updated list
        header("Location: edit_user_songs.php?user_id=$user_id");
        exit();
    } else {
        echo "<p>Error removing song: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Songs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 800px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .form-container {
            margin-top: 20px;
        }
        .form-container input, .form-container select {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Songs for <?php echo htmlspecialchars($user['UserName']); ?></h1>
        <a href="admin_dashboard.php">Back to Dashboard</a>

        <h2>Assigned Songs</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($userMusicResult->num_rows > 0): ?>
                    <?php while ($song = $userMusicResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($song['Title']); ?></td>
                            <td><?php echo htmlspecialchars($song['Artist']); ?></td>
                            <td>
                                <a href="?remove_song=<?php echo $song['Music_id']; ?>&user_id=<?php echo $user_id; ?>">Remove</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No songs assigned to this user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Assign New Song</h2>
        <div class="form-container">
            <form method="post" action="">
                <select name="music_id" required>
                    <?php
                    $musicResult->data_seek(0); // Reset pointer to re-fetch music
                    while ($music = $musicResult->fetch_assoc()): ?>
                        <option value="<?php echo $music['Music_id']; ?>">
                            <?php echo htmlspecialchars($music['Title']); ?> by <?php echo htmlspecialchars($music['Artist']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="assign_song">Assign Song</button>
            </form>
        </div>
    </div>
</body>
</html>