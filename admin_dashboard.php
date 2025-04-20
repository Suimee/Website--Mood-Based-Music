<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

// Handle Add Song
if (isset($_POST['add_song'])) {
    $title = htmlspecialchars($_POST['title']);
    $artist = htmlspecialchars($_POST['artist']);
    $mood = htmlspecialchars($_POST['mood']);

    $insertSong = $conn->prepare("INSERT INTO Music (Title, Artist, Mood) VALUES (?, ?, ?)");
    $insertSong->bind_param("sss", $title, $artist, $mood);

    if ($insertSong->execute()) {
        $message = "<p class='success'>Song added successfully!</p>";
    } else {
        $error = "<p class='error'>Error adding song: " . $conn->error . "</p>";
    }
}

// Handle Remove Song
if (isset($_GET['remove_song'])) {
    $music_id = $_GET['remove_song'];

    // First, remove the song from the UserMusic table to avoid foreign key constraint errors
    $deleteUserMusic = $conn->prepare("DELETE FROM UserMusic WHERE Music_id = ?");
    $deleteUserMusic->bind_param("i", $music_id);
    $deleteUserMusic->execute();

    // Then, remove the song from the Music table
    $deleteSong = $conn->prepare("DELETE FROM Music WHERE Music_id = ?");
    $deleteSong->bind_param("i", $music_id);

    if ($deleteSong->execute()) {
        $message = "<p class='success'>Song removed successfully!</p>";
    } else {
        $error = "<p class='error'>Error removing song: " . $conn->error . "</p>";
    }
}

// Handle Update User Info (only for users, not admins)
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = htmlspecialchars($_POST['username']);

    // Check if the user is not an admin
    $checkRole = $conn->prepare("SELECT Role FROM USERS WHERE User_id = ?");
    $checkRole->bind_param("i", $user_id);
    $checkRole->execute();
    $checkRoleResult = $checkRole->get_result();
    $userRole = $checkRoleResult->fetch_assoc()['Role'];

    if ($userRole === 'User') {
        $updateUser = $conn->prepare("UPDATE USERS SET UserName = ? WHERE User_id = ?");
        $updateUser->bind_param("si", $username, $user_id);

        if ($updateUser->execute()) {
            $message = "<p class='success'>User updated successfully!</p>";
        } else {
            $error = "<p class='error'>Error updating user: " . $conn->error . "</p>";
        }
    } else {
        $error = "<p class='error'>You cannot update the username of an admin.</p>";
    }
}

// Handle Assign Song to User
if (isset($_POST['assign_song'])) {
    $user_id = $_POST['user_id'];
    $music_id = $_POST['music_id'];

    $assignSong = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id) VALUES (?, ?)");
    $assignSong->bind_param("ii", $user_id, $music_id);

    if ($assignSong->execute()) {
        $message = "<p class='success'>Song assigned successfully!</p>";
    } else {
        $error = "<p class='error'>Error assigning song: " . $conn->error . "</p>";
    }
}

// Handle Delete Feedback
if (isset($_GET['delete_feedback'])) {
    $feedback_id = $_GET['delete_feedback'];
    
    $deleteQuery = $conn->prepare("DELETE FROM Feedback WHERE Feedback_id = ?");
    $deleteQuery->bind_param("i", $feedback_id);
    
    if ($deleteQuery->execute()) {
        $message = "<p class='success'>Feedback deleted successfully!</p>";
    } else {
        $error = "<p class='error'>Error deleting feedback: " . $conn->error . "</p>";
    }
}

// Fetch only users (not admins)
$usersQuery = "SELECT User_id, UserName, Role FROM USERS WHERE Role = 'User'";
$usersResult = $conn->query($usersQuery);

// Fetch all music
$musicQuery = "SELECT Music_id, Title, Artist, Mood FROM Music";
$musicResult = $conn->query($musicQuery);

// Fetch user-music associations
$userMusicQuery = "SELECT UserMusic.User_id, UserMusic.Music_id, Music.Title, Music.Artist 
                   FROM UserMusic 
                   JOIN Music ON UserMusic.Music_id = Music.Music_id";
$userMusicResult = $conn->query($userMusicQuery);

// Store user-music associations in an array
$userMusic = [];
while ($row = $userMusicResult->fetch_assoc()) {
    $userMusic[$row['User_id']][] = $row;
}

// Fetch all feedback
$feedbackQuery = "SELECT f.Feedback_id, f.User_id, f.Music_id, f.Feedback_text, f.Rating, f.Created_at, 
                  u.UserName, m.Title, m.Artist 
                  FROM Feedback f
                  JOIN Users u ON f.User_id = u.User_id
                  JOIN Music m ON f.Music_id = m.Music_id
                  ORDER BY f.Created_at DESC";
$feedbackResult = $conn->query($feedbackQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Music Player</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        /* General Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            margin: 0;
            color: #fff;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 90%;
            max-width: 1200px;
            margin: 2rem auto;
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

        h1, h2, h3 {
            color: #fff;
            text-align: center;
        }

        h1 {
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
            font-weight: 600;
        }

        h2 {
            margin-top: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.5rem;
        }

        a {
            color: #ff6f61;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
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
            margin: 1.5rem 0;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
        }

        .form-container input, 
        .form-container select,
        .form-container textarea {
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

        .form-container input::placeholder, 
        .form-container select,
        .form-container textarea::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-container input:focus, 
        .form-container select:focus,
        .form-container textarea:focus {
            background: rgba(255, 255, 255, 0.3);
        }

        .form-container button {
            width: 100%;
            padding: 0.75rem;
            background: #ff6f61;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 0.5rem;
        }

        .form-container button:hover {
            background: #ff4a3d;
        }
        select {
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
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1em;
}

select:focus {
    background: rgba(255, 255, 255, 0.3);
}

select option {
    background: #2a5298;
    color: #fff;
}

        .success {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Feedback specific styles */
        .feedback-rating {
            display: inline-block;
            color: #ffc107;
            font-weight: bold;
        }
        
        .feedback-text {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        .feedback-date {
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .delete-feedback {
            color: #dc3545;
            margin-left: 10px;
        }

        .action-links {
            white-space: nowrap;
        }

        .action-links a {
            margin-right: 10px;
        }

        small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <a href="logout.php">Logout</a>
        </div>

        <?php if (isset($message)) echo $message; ?>
        <?php if (isset($error)) echo $error; ?>

        <h2>Manage Songs</h2>
        <div class="form-container">
            <h3>Add New Song</h3>
            <form method="post" action="">
                <input type="text" name="title" placeholder="Title" required>
                <input type="text" name="artist" placeholder="Artist" required>
                <input type="text" name="mood" placeholder="Mood" required>
                <button type="submit" name="add_song">Add Song</button>
            </form>
        </div>

        <h3>Song List</h3>
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
                <?php while ($music = $musicResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($music['Title']); ?></td>
                        <td><?php echo htmlspecialchars($music['Artist']); ?></td>
                        <td><?php echo htmlspecialchars($music['Mood']); ?></td>
                        <td class="action-links">
                            <a href="edit_song.php?music_id=<?php echo $music['Music_id']; ?>">Edit</a>
                            <a href="?remove_song=<?php echo $music['Music_id']; ?>" 
                               onclick="return confirm('Are you sure you want to remove this song?')">Remove</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Manage Users</h2>
        <div class="form-container">
            <h3>Update User Info</h3>
            <form method="post" action="">
                <select name="user_id" required>
                    <option value="">Select a user</option>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                        <option value="<?php echo $user['User_id']; ?>">
                            <?php echo htmlspecialchars($user['UserName']); ?> (<?php echo htmlspecialchars($user['Role']); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="username" placeholder="New Username" required>
                <button type="submit" name="update_user">Update User</button>
            </form>
        </div>

        <h3>User List</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $usersResult->data_seek(0); // Reset pointer to re-fetch users
                while ($user = $usersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                        <td><?php echo htmlspecialchars($user['Role']); ?></td>
                        <td class="action-links">
                            <a href="edit_user_songs.php?user_id=<?php echo $user['User_id']; ?>">Edit Songs</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Assign Song to User</h2>
        <div class="form-container">
            <form method="post" action="">
                <select name="user_id" required>
                    <option value="">Select a user</option>
                    <?php
                    $usersResult->data_seek(0); // Reset pointer to re-fetch users
                    while ($user = $usersResult->fetch_assoc()): ?>
                        <option value="<?php echo $user['User_id']; ?>">
                            <?php echo htmlspecialchars($user['UserName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="music_id" required>
                    <option value="">Select a song</option>
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
        <div class="form-container">
    <h3>Update User Info</h3>
    <form method="post" action="">
        <select name="user_id" required>
            <option value="">Select a user</option>
            <?php 
            $usersResult->data_seek(0); // Reset pointer to re-fetch users
            while ($user = $usersResult->fetch_assoc()): ?>
                <option value="<?php echo $user['User_id']; ?>">
                    <?php echo htmlspecialchars($user['UserName']); ?> (<?php echo htmlspecialchars($user['Role']); ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <input type="text" name="username" placeholder="New Username" required>
        <button type="submit" name="update_user">Update User</button>
    </form>
</div>

        <h3>User Song List</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Songs</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $usersResult->data_seek(0); // Reset pointer to re-fetch users
                while ($user = $usersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                        <td>
                            <?php if (isset($userMusic[$user['User_id']])): ?>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php foreach ($userMusic[$user['User_id']] as $song): ?>
                                        <li>
                                            <?php echo htmlspecialchars($song['Title']); ?> by <?php echo htmlspecialchars($song['Artist']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                No songs assigned.
                            <?php endif; ?>
                        </td>
                        <td class="action-links">
                            <a href="edit_user_songs.php?user_id=<?php echo $user['User_id']; ?>">Edit Songs</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>User Feedback</h2>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Song</th>
                    <th>Rating</th>
                    <th>Feedback</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($feedback = $feedbackResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($feedback['UserName']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($feedback['Title']); ?><br>
                            <small>by <?php echo htmlspecialchars($feedback['Artist']); ?></small>
                        </td>
                        <td>
                            <span class="feedback-rating">
                                <?php echo str_repeat('★', $feedback['Rating']); ?>
                                <?php echo str_repeat('☆', 5 - $feedback['Rating']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="feedback-text">
                                <?php echo htmlspecialchars($feedback['Feedback_text']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="feedback-date">
                                <?php echo date('M j, Y g:i a', strtotime($feedback['Created_at'])); ?>
                            </span>
                        </td>
                        <td class="action-links">
                            <a href="?delete_feedback=<?php echo $feedback['Feedback_id']; ?>" 
                               class="delete-feedback" 
                               onclick="return confirm('Are you sure you want to delete this feedback?')">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
$conn->close();
?>