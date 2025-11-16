<?php
// index.php
session_start();
require_once "pdo.php";

echo "<h1>Profile Database</h1>";

// Flash messages
if (isset($_SESSION['error'])) {
    echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p style="color:green">'.htmlentities($_SESSION['success'])."</p>";
    unset($_SESSION['success']);
}

// Logged in?
if (isset($_SESSION['name'])) {
    echo "<p>Welcome, ".htmlentities($_SESSION['name'])." | <a href='logout.php'>Logout</a></p>";
    echo "<p><a href='add.php'>Add New Profile</a></p>";
} else {
    echo "<p><a href='login.php'>Please log in</a></p>";
}

// Fetch all profiles
$stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
echo "<table border='1'><tr><th>Name</th><th>Headline</th><th>Action</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>";
    echo htmlentities($row['first_name'])." ".htmlentities($row['last_name']);
    echo "</td><td>";
    echo htmlentities($row['headline']);
    echo "</td><td>";
    if (isset($_SESSION['user_id'])) {
        echo "<a href='edit.php?profile_id=".$row['profile_id']."'>Edit</a> / ";
        echo "<a href='delete.php?profile_id=".$row['profile_id']."'>Delete</a>";
    }
    echo "</td></tr>";
}
echo "</table>";
