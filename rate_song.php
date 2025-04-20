<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'your_database_name');
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['music_id']) || !isset($data['rating'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

$music_id = intval($data['music_id']);
$rating = $data['rating'] === 'like' ? 1 : 0; // 1 for like, 0 for dislike
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

// Check if the user has already rated this song
$checkQuery = $conn->prepare("SELECT * FROM UserMusic WHERE User_id = ? AND Music_id = ?");
$checkQuery->bind_param("ii", $user_id, $music_id);
$checkQuery->execute();
$result = $checkQuery->get_result();

if ($result->num_rows > 0) {
    // Update existing rating
    $updateQuery = $conn->prepare("UPDATE UserMusic SET Rating = ? WHERE User_id = ? AND Music_id = ?");
    $updateQuery->bind_param("iii", $rating, $user_id, $music_id);
    $updateQuery->execute();
} else {
    // Insert new rating
    $insertQuery = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id, Rating) VALUES (?, ?, ?)");
    $insertQuery->bind_param("iii", $user_id, $music_id, $rating);
    $insertQuery->execute();
}

// Return success response
echo json_encode(['status' => 'success', 'message' => 'Rating saved']);

// Close database connection
$conn->close();
?>