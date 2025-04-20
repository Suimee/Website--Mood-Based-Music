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

// Get the selected mood from the form submission or URL parameter
if (isset($_POST['mood'])) {
    $selected_mood = $_POST['mood'];
} elseif (isset($_GET['mood'])) {
    $selected_mood = $_GET['mood'];
} else {
    header("Location: select_mood.php");
    exit();
}

// Handle song removal
if (isset($_GET['remove_song'])) {
    $music_id = $_GET['remove_song'];
    $removeQuery = $conn->prepare("DELETE FROM UserMusic WHERE User_id = ? AND Music_id = ?");
    $removeQuery->bind_param("ii", $user_id, $music_id);
    $removeQuery->execute();
    header("Location: music_player.php?mood=" . urlencode($selected_mood));
    exit();
}

// Handle song assignment
if (isset($_GET['assign_song'])) {
    $music_id = $_GET['assign_song'];
    
    // Check if the song is already assigned to the user
    $checkQuery = $conn->prepare("SELECT * FROM UserMusic WHERE User_id = ? AND Music_id = ?");
    $checkQuery->bind_param("ii", $user_id, $music_id);
    $checkQuery->execute();
    $checkResult = $checkQuery->get_result();
    
    if ($checkResult->num_rows == 0) {
        // Assign the song to the user
        $assignQuery = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id) VALUES (?, ?)");
        $assignQuery->bind_param("ii", $user_id, $music_id);
        $assignQuery->execute();
    }
    
    header("Location: music_player.php?mood=" . urlencode($selected_mood));
    exit();
}

// Handle feedback submission
if (isset($_POST['submit_feedback'])) {
    $music_id = $_POST['music_id'];
    $feedback_text = $_POST['feedback_text'];
    $rating = $_POST['rating'];
    
    $feedbackQuery = $conn->prepare("INSERT INTO Feedback (User_id, Music_id, Feedback_text, Rating) VALUES (?, ?, ?, ?)");
    $feedbackQuery->bind_param("iisi", $user_id, $music_id, $feedback_text, $rating);
    $feedbackQuery->execute();
    
    // Redirect back to music player with the same mood
    header("Location: music_player.php?mood=" . urlencode($selected_mood));
    exit();
}

// Fetch songs assigned to the logged-in user for the selected mood
$userMusicQuery = "SELECT Music.Music_id, Music.Title, Music.Artist, Music.Mood 
                   FROM UserMusic 
                   JOIN Music ON UserMusic.Music_id = Music.Music_id 
                   WHERE UserMusic.User_id = ? AND Music.Mood = ?";
$userMusicStmt = $conn->prepare($userMusicQuery);
$userMusicStmt->bind_param("is", $user_id, $selected_mood);
$userMusicStmt->execute();
$userMusicResult = $userMusicStmt->get_result();

// Fetch random songs of the selected mood for the right sidebar
$randomSongQuery = "SELECT Music_id, Title, Artist, Mood FROM Music WHERE Mood = ? ORDER BY RAND() LIMIT 5";
$randomSongStmt = $conn->prepare($randomSongQuery);
$randomSongStmt->bind_param("s", $selected_mood);
$randomSongStmt->execute();
$randomSongResult = $randomSongStmt->get_result();

// Define background colors based on mood
$moodColors = [
    'Happy' => '#FFD700', // Gold
    'Sad' => '#1E90FF',   // Dodger Blue
    'Energetic' => '#FF4500', // Orange Red
    'Relaxed' => '#32CD32', // Lime Green
    'Romantic' => '#FF69B4', // Hot Pink
    'Calm' => '#87CEEB', // Sky Blue
];

// Get the background color based on the selected mood
$backgroundColor = $moodColors[$selected_mood] ?? '#28a745'; // Default to green if mood not found
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background: <?php echo $backgroundColor; ?>;
            color: #fff;
        }

        .sidebar {
            width: 400px;
            background: rgba(0, 0, 0, 0.7);
            padding: 1rem;
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
            padding: 2rem;
        }

        h1, h2, h3 {
            color: #fff;
            text-align: center;
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

        .player-container {
            text-align: center;
            margin-top: 20px;
            width: 80%;
        }

        .player-container audio {
            width: 100%;
            margin-top: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .play-button {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 0.9rem;
        }

        .play-button:hover {
            background: #218838;
        }

        .remove-button {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 0.9rem;
        }

        .remove-button:hover {
            background: #c82333;
        }

        .assign-to-me-button {
            background:rgba(255, 255, 255, 0.05);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 0.9rem;
        }

        .assign-to-me-button:hover {
            background:rgb(217, 65, 0);
        }

        .feedback-button {
            background:rgb(255, 255, 255);
            color: #000;
            border: none;
            border-radius: 25px;
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            font-size: 0.9rem;
        }

        .feedback-button:hover {
            background:rgb(217, 65, 0);
        }

        .back-button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #000;
            color: white;
            text-align: center;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        .back-button:hover {
            background: #333;
        }

        /* Feedback Modal Styles */
        .feedback-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .feedback-content {
            background-color: #4e342e;
            padding: 20px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .feedback-content h3 {
            margin-top: 0;
            color: #ffe0b2;
            text-align: center;
        }

        .music-info {
            background-color: #5d4037;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #d7ccc8;
        }

        .music-info p {
            margin: 5px 0;
            color: #fbe9e7;
        }

        .rating-container, .feedback-container {
            margin-bottom: 15px;
        }

        .rating-container label, .feedback-container label {
            display: block;
            margin-bottom: 8px;
            color: #ffab91;
            font-weight: bold;
        }

        .rating-container select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #d7ccc8;
            background-color: #efebe9;
            color: #3e2723;
        }

        .feedback-container textarea {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #d7ccc8;
            background-color: #efebe9;
            color: #3e2723;
            min-height: 100px;
            resize: vertical;
        }

        .feedback-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .submit-feedback {
            background-color: #8d6e63;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .submit-feedback:hover {
            background-color: #6d4c41;
        }

        .cancel-feedback {
            background-color: #5d4037;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .cancel-feedback:hover {
            background-color: #3e2723;
        }

        .success-message {
            color: #4caf50;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Left Sidebar: User's Assigned Songs (Queue) -->
    <div class="sidebar">
        <h2>Your Queue</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Mood</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($song = $userMusicResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($song['Title']); ?></td>
                        <td><?php echo htmlspecialchars($song['Artist']); ?></td>
                        <td><?php echo htmlspecialchars($song['Mood']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="play-button" onclick="playYouTubeSong('<?php echo htmlspecialchars($song['Title']); ?>', '<?php echo htmlspecialchars($song['Artist']); ?>', <?php echo $song['Music_id']; ?>)">Play</button>
                                <button class="feedback-button" onclick="openFeedbackModal('<?php echo htmlspecialchars($song['Title']); ?>', '<?php echo htmlspecialchars($song['Artist']); ?>', '<?php echo htmlspecialchars($song['Mood']); ?>', <?php echo $song['Music_id']; ?>)">Feedback</button>
                                <a href="?remove_song=<?php echo $song['Music_id']; ?>&mood=<?php echo urlencode($selected_mood); ?>" onclick="return confirm('Are you sure you want to remove this song?');">
                                    <button class="remove-button">Remove</button>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Back to Dashboard Button -->
        <a href="user_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <!-- Main Content: Music Player -->
    <div class="main-content">
        <h1>Music Player</h1>
        <div class="player-container" id="player-container">
            <!-- YouTube Player will be embedded here -->
        </div>
    </div>

    <!-- Right Sidebar: Random Songs of Selected Mood -->
    <div class="sidebar">
        <h2>Random Songs (<?php echo htmlspecialchars($selected_mood); ?>)</h2>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Mood</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($song = $randomSongResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($song['Title']); ?></td>
                        <td><?php echo htmlspecialchars($song['Artist']); ?></td>
                        <td><?php echo htmlspecialchars($song['Mood']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="play-button" onclick="playYouTubeSong('<?php echo htmlspecialchars($song['Title']); ?>', '<?php echo htmlspecialchars($song['Artist']); ?>', <?php echo $song['Music_id']; ?>)">Play</button>
                                <a href="?assign_song=<?php echo $song['Music_id']; ?>&mood=<?php echo urlencode($selected_mood); ?>">
                                    <button class="assign-to-me-button">Assign to Me</button>
                                </a>
                                <button class="feedback-button" onclick="openFeedbackModal('<?php echo htmlspecialchars($song['Title']); ?>', '<?php echo htmlspecialchars($song['Artist']); ?>', '<?php echo htmlspecialchars($song['Mood']); ?>', <?php echo $song['Music_id']; ?>)">Feedback</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="feedback-modal">
        <div class="feedback-content">
            <h3>Provide Feedback</h3>
            
            <div class="music-info">
                <p><strong>Title:</strong> <span id="feedback-song-title"></span></p>
                <p><strong>Artist:</strong> <span id="feedback-song-artist"></span></p>
                <p><strong>Mood:</strong> <span id="feedback-song-mood"></span></p>
            </div>
            
            <form id="feedbackForm" method="POST" action="music_player.php?mood=<?php echo urlencode($selected_mood); ?>">
                <input type="hidden" id="feedback-music-id" name="music_id" value="">
                
                <div class="rating-container">
                    <label for="rating">Rating:</label>
                    <select id="rating" name="rating" required>
                        <option value="">Select a rating</option>
                        <option value="1">1 ★</option>
                        <option value="2">2 ★★</option>
                        <option value="3">3 ★★★</option>
                        <option value="4">4 ★★★★</option>
                        <option value="5">5 ★★★★★</option>
                    </select>
                </div>
                
                <div class="feedback-container">
                    <label for="feedback_text">Your Feedback:</label>
                    <textarea id="feedback_text" name="feedback_text" placeholder="Enter your feedback about this song..." required></textarea>
                </div>
                
                <div class="feedback-buttons">
                    <button type="button" class="cancel-feedback" onclick="closeFeedbackModal()">Cancel</button>
                    <button type="submit" class="submit-feedback" name="submit_feedback">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- YouTube Data API Integration -->
    <script>
        const apiKey = 'AIzaSyBaLKRh22hFmwg2b9SQyI8PdisNlKpRTLY'; // Replace with your YouTube API Key

        // Function to play a YouTube song
        function playYouTubeSong(title, artist, musicId) {
            const query = `${title} ${artist}`;
            const playerContainer = document.getElementById('player-container');

            // Clear previous player
            playerContainer.innerHTML = '';
            
            // Search for the song on YouTube
            fetch(`https://www.googleapis.com/youtube/v3/search?part=snippet&q=${encodeURIComponent(query)}&key=${apiKey}`)
                .then(response => response.json())
                .then(data => {
                    if (data.items.length > 0) {
                        const videoId = data.items[0].id.videoId;
                        // Embed the YouTube player
                        playerContainer.innerHTML = `
                            <iframe
                                width="560"
                                height="315"
                                src="https://www.youtube.com/embed/${videoId}?autoplay=1"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                            <p>Now playing: ${title} by ${artist}</p>
                        `;
                    } else {
                        playerContainer.innerHTML = '<p>No matching video found on YouTube.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching YouTube video:', error);
                    playerContainer.innerHTML = '<p>Error loading YouTube video.</p>';
                });
        }

        // Feedback modal functions
        function openFeedbackModal(title, artist, mood, musicId) {
            document.getElementById('feedback-song-title').textContent = title;
            document.getElementById('feedback-song-artist').textContent = artist;
            document.getElementById('feedback-song-mood').textContent = mood;
            document.getElementById('feedback-music-id').value = musicId;
            document.getElementById('feedbackModal').style.display = 'flex';
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
            document.getElementById('feedbackForm').reset();
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('feedbackModal')) {
                closeFeedbackModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>