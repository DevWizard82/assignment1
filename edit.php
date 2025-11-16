<?php
// edit.php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) {
    die("ACCESS DENIED");
}

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$profile_id = $_GET['profile_id'];
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id=:pid");
$stmt->execute(array(':pid'=>$profile_id));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $_SESSION['error'] = "Profile not found";
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name']) && isset($_POST['last_name'])) {
    $stmt = $pdo->prepare('UPDATE Profile SET first_name=:fn, last_name=:ln, email=:em, headline=:he, summary=:su WHERE profile_id=:pid');
    $stmt->execute(array(
        ':fn'=>$_POST['first_name'],
        ':ln'=>$_POST['last_name'],
        ':em'=>$_POST['email'],
        ':he'=>$_POST['headline'],
        ':su'=>$_POST['summary'],
        ':pid'=>$profile_id
    ));
    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}
?>

<h1>Edit Profile</h1>

<form method="post">
First Name: <input type="text" name="first_name" value="<?= htmlentities($row['first_name']) ?>"><br>
Last Name: <input type="text" name="last_name" value="<?= htmlentities($row['last_name']) ?>"><br>
Email: <input type="text" name="email" value="<?= htmlentities($row['email']) ?>"><br>
Headline: <input type="text" name="headline" value="<?= htmlentities($row['headline']) ?>"><br>
Summary:<br>
<textarea name="summary" rows="8" cols="40"><?= htmlentities($row['summary']) ?></textarea><br>
<input type="submit" value="Save">
</form>
