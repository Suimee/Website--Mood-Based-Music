<?php
if (isset($_GET['success']) && $_GET['success'] === 'song_assigned') {
    echo "<p style='color: green;'>Song assigned successfully!</p>";
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'already_assigned') {
        echo "<p style='color: red;'>This song is already assigned to you.</p>";
    } elseif ($_GET['error'] === 'assignment_failed') {
        echo "<p style='color: red;'>Failed to assign the song. Please try again.</p>";
    }
}
?>