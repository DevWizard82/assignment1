<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) {
    // Not logged in → show link to login
    echo '<title>bc52c173</title>';
    echo '<a href="login.php">Please log in</a>';
    exit();
}

// Logged in → show profiles
echo "<h1>Profile Database</h1>";
echo "<p>Welcome, ".htmlentities($_SESSION['name'])." | <a href='logout.php'>Logout</a></p>";
echo "<p><a href='add.php'>Add New Profile</a></p>";

// Fetch and display profiles
$stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM Profile");
echo "<table border='1'><tr><th>Name</th><th>Headline</th><th>Action</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>".htmlentities($row['first_name'])." ".htmlentities($row['last_name'])."</td>";
    echo "<td>".htmlentities($row['headline'])."</td>";
    echo "<td><a href='edit.php?profile_id=".$row['profile_id']."'>Edit</a> / ";
    echo "<a href='delete.php?profile_id=".$row['profile_id']."'>Delete</a></td></tr>";
}
echo "</table>";
