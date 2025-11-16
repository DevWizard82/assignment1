<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) die("Not logged in");

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id=:pid AND user_id=:uid");
$stmt->execute(array(':pid'=>$_GET['profile_id'], ':uid'=>$_SESSION['user_id']));
$row = $stmt->fetch();

if (!$row) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name'])) {
    // Validation
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email address must contain @";
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    $stmt = $pdo->prepare('UPDATE Profile SET first_name=:fn,last_name=:ln,email=:em,headline=:he,summary=:su WHERE profile_id=:pid AND user_id=:uid');
    $stmt->execute(array(
        ':fn'=>$_POST['first_name'],
        ':ln'=>$_POST['last_name'],
        ':em'=>$_POST['email'],
        ':he'=>$_POST['headline'],
        ':su'=>$_POST['summary'],
        ':pid'=>$_POST['profile_id'],
        ':uid'=>$_SESSION['user_id']
    ));
    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}
?>

<html>
<head><title>bc52c173</title></head>
<body>
<h1>Editing Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>";
    unset($_SESSION['error']);
}
?>

<form method="post">
<input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
First Name: <input type="text" name="first_name" value="<?= htmlentities($row['first_name']) ?>"><br><br>
Last Name: <input type="text" name="last_name" value="<?= htmlentities($row['last_name']) ?>"><br><br>
Email: <input type="text" name="email" value="<?= htmlentities($row['email']) ?>"><br><br>
Headline: <input type="text" name="headline" value="<?= htmlentities($row['headline']) ?>"><br><br>
Summary:<br>
<textarea name="summary" rows="8" cols="50"><?= htmlentities($row['summary']) ?></textarea><br><br>
<input type="submit" value="Save">
<a href="index.php">Cancel</a>
</form>
</body>
</html>
