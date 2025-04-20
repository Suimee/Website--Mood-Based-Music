<?php
session_start();
include 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$music_id = $data['music_id'];
$mood = $data['mood'];
$user_id = $_SESSION['user_id'];

// Allocate the song to the user's queue
$stmt = $conn->prepare("INSERT INTO UserMusic (User_id, Music_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $music_id);

if ($stmt->execute()) {
    // Fetch the allocated song details
    $songQuery = $conn->prepare("SELECT Title, Artist FROM Music WHERE Music_id = ?");
    $songQuery->bind_param("i", $music_id);
    $songQuery->execute();
    $songResult = $songQuery->get_result();
    $song = $songResult->fetch_assoc();

    echo json_encode(['success' => true, 'song' => $song]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>