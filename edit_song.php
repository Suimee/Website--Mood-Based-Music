<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

// Fetch song details
if (isset($_GET['music_id'])) {
    $music_id = $_GET['music_id'];
    $songQuery = $conn->prepare("SELECT * FROM Music WHERE Music_id = ?");
    $songQuery->bind_param("i", $music_id);
    $songQuery->execute();
    $songResult = $songQuery->get_result();
    $song = $songResult->fetch_assoc();

    if (!$song) {
        echo "<p>Song not found!</p>";
        exit();
    }
} else {
    echo "<p>No song selected!</p>";
    exit();
}

// Handle Update Song
if (isset($_POST['update_song'])) {
    $title = htmlspecialchars($_POST['title']);
    $artist = htmlspecialchars($_POST['artist']);
    $mood = htmlspecialchars($_POST['mood']);

    $updateSong = $conn->prepare("UPDATE Music SET Title = ?, Artist = ?, Mood = ? WHERE Music_id = ?");
    $updateSong->bind_param("sssi", $title, $artist, $mood, $music_id);

    if ($updateSong->execute()) {
        // Redirect to the admin dashboard after updating the song
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "<p>Error updating song: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Song</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 600px;
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
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Song</h1>
        <a href="admin_dashboard.php">Back to Dashboard</a>

        <div class="form-container">
            <form method="post" action="">
                <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($song['Title']); ?>" required>
                <input type="text" name="artist" placeholder="Artist" value="<?php echo htmlspecialchars($song['Artist']); ?>" required>
                <input type="text" name="mood" placeholder="Mood" value="<?php echo htmlspecialchars($song['Mood']); ?>" required>
                <button type="submit" name="update_song">Update Song</button>
            </form>
        </div>
    </div>
</body>
</html>