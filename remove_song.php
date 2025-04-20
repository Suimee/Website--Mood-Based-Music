<?php
session_start();
include 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$music_id = $data['music_id'];
$user_id = $_SESSION['user_id'];

// Remove the song from the user's queue
$stmt = $conn->prepare("DELETE FROM UserMusic WHERE User_id = ? AND Music_id = ?");
$stmt->bind_param("ii", $user_id, $music_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>