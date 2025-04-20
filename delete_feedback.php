<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

include 'connect.php';

if (isset($_GET['id'])) {
    $feedback_id = $_GET['id'];
    
    $deleteQuery = $conn->prepare("DELETE FROM Feedback WHERE Feedback_id = ?");
    $deleteQuery->bind_param("i", $feedback_id);
    
    if ($deleteQuery->execute()) {
        $_SESSION['message'] = "Feedback deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting feedback: " . $conn->error;
    }
}

header("Location: admin_dashboard.php");
exit();
?>