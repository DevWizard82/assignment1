<?php
session_start();
require_once "pdo.php";

echo "<!DOCTYPE html><html><head><title>bc52c173</title></head><body>";
echo "<h1>Profile Database</h1>";

if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>";
    unset($_SESSION['error']);
}

if (!isset($_SESSION['user_id'])) {
    echo '<a href="login.php">Please log in</a>';
    echo "</body></html>";
    exit();
}

echo "<p>Welcome, ".htmlentities($_SESSION['name'])." | <a href='logout.php'>Logout</a></p>";
echo "<p><a href='add.php'>Add New Profile</a></p>";

// Fetch profiles
$stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline, user_id FROM Profile");
echo "<table border='1'><tr><th>Name</th><th>Headline</th><th>Action</th></tr>";
while ($row = $stmt->fetch()) {
    echo "<tr>";
    echo "<td><a href='view.php?profile_id=".$row['profile_id']."'>"
         .htmlentities($row['first_name'])." ".htmlentities($row['last_name'])."</a></td>";
    echo "<td>".htmlentities($row['headline'])."</td>";
    echo "<td>";
    if ($row['user_id'] == $_SESSION['user_id']) {
        echo "<a href='edit.php?profile_id=".$row['profile_id']."'>Edit</a> / ";
        echo "<a href='delete.php?profile_id=".$row['profile_id']."'>Delete</a>";
    }
    echo "</td></tr>";
}
echo "</table></body></html>";
