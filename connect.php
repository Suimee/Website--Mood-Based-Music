<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "mood_based_music";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>