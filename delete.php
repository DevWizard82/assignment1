<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) die("Not logged in");

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

// Fetch profile
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id=:pid AND user_id=:uid");
$stmt->execute(array(':pid'=>$_GET['profile_id'], ':uid'=>$_SESSION['user_id']));
$row = $stmt->fetch();

if (!$row) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM Profile WHERE profile_id=:pid AND user_id=:uid");
    $stmt->execute(array(':pid'=>$_POST['profile_id'], ':uid'=>$_SESSION['user_id']));
    $_SESSION['success'] = "Profile deleted";
    header("Location: index.php");
    return;
}
?>

<html>
<head><title>bc52c173</title></head>
<body>
<h1>Delete Profile</h1>
<p>Are you sure you want to delete this profile?</p>

<form method="post">
<input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
<input type="submit" name="delete" value="Delete">
<a href="index.php">Cancel</a>
</form>
</body>
</html>
