<?php
session_start();
if (!isset($_SESSION['userName'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['firstName']); ?>!</h1>
    <p>You are logged in as <?php echo htmlspecialchars($_SESSION['userName']); ?>.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
