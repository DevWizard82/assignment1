<?php
session_start();
require_once "pdo.php";

if (! isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid");
$stmt->execute(array(':pid' => $_GET['profile_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (! $profile) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

// load positions ordered by rank
$stmt = $pdo->prepare("SELECT year, description FROM Position
                       WHERE profile_id = :pid
                       ORDER BY rank");
$stmt->execute(array(':pid' => $_GET['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <title>anas berrqia - View Profile</title>
  <link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body class="container">
  <h1>Profile Information</h1>

  <p>First Name: <?= htmlentities($profile['first_name']) ?></p>
  <p>Last Name: <?= htmlentities($profile['last_name']) ?></p>
  <p>Email: <?= htmlentities($profile['email']) ?></p>
  <p>Headline: <br><?= htmlentities($profile['headline']) ?></p>
  <p>Summary: <br><?= htmlentities($profile['summary']) ?></p>

  <?php if (count($positions) > 0): ?>
    <p>Positions:</p>
    <ul>
      <?php foreach ($positions as $pos): ?>
        <li><?= htmlentities($pos['year']) ?>: <?= nl2br(htmlentities($pos['description'])) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <p><a href="index.php">Back to Index</a></p>
</body>
</html>
