<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) die("ACCESS DENIED");

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

// load profile and ensure owner
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :pid AND user_id = :uid");
$stmt->execute(array(':pid' => $_GET['profile_id'], ':uid' => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (! $row) {
    $_SESSION['error'] = "Could not load profile";
    header("Location: index.php");
    return;
}

// load existing positions
$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :pid ORDER BY rank");
$stmt->execute(array(':pid' => $_GET['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['first_name'])) {
    // Basic profile validation
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

    // Validate positions posted
    function validatePos() {
        for ($i=1; $i<=9; $i++) {
            if (! isset($_POST['year'.$i])) continue;
            if (! isset($_POST['desc'.$i])) continue;
            $year = trim($_POST['year'.$i]);
            $desc = trim($_POST['desc'.$i]);
            if (strlen($year) == 0 || strlen($desc) == 0) return "All fields are required";
            if (! is_numeric($year)) return "Position year must be numeric";
        }
        return true;
    }
    $val = validatePos();
    if ($val !== true) {
        $_SESSION['error'] = $val;
        header("Location: edit.php?profile_id=".$_POST['profile_id']);
        return;
    }

    // Update profile row
    $stmt = $pdo->prepare('UPDATE Profile SET first_name=:fn, last_name=:ln, email=:em, headline=:he, summary=:su
                           WHERE profile_id=:pid AND user_id=:uid');
    $stmt->execute(array(
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'],
        ':pid' => $_POST['profile_id'],
        ':uid' => $_SESSION['user_id']
    ));

    // Delete old positions
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id = :pid');
    $stmt->execute(array(':pid' => $_POST['profile_id']));

    // Insert new positions in posted order
    $rank = 1;
    for ($i=1; $i<=9; $i++) {
        if (! isset($_POST['year'.$i])) continue;
        if (! isset($_POST['desc'.$i])) continue;
        $year = trim($_POST['year'.$i]);
        $desc = trim($_POST['desc'.$i]);

        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description)
            VALUES (:pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $_POST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ));
        $rank++;
    }

    $_SESSION['success'] = "Profile updated";
    header("Location: index.php");
    return;
}
?>
<!doctype html>
<html>
<head>
  <title>anas berrqia - Edit Profile</title>
  <link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script
    src="https://code.jquery.com/jquery-3.2.1.js"></script>
</head>
<body class="container">
  <h1>Editing Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="post" class="form-horizontal">
  <input type="hidden" name="profile_id" value="<?= htmlentities($row['profile_id']) ?>">
  <div class="form-group">
    <label>First Name:</label>
    <input type="text" name="first_name" class="form-control"
           value="<?= htmlentities($row['first_name']) ?>">
  </div>
  <div class="form-group">
    <label>Last Name:</label>
    <input type="text" name="last_name" class="form-control"
           value="<?= htmlentities($row['last_name']) ?>">
  </div>
  <div class="form-group">
    <label>Email:</label>
    <input type="text" name="email" class="form-control"
           value="<?= htmlentities($row['email']) ?>">
  </div>
  <div class="form-group">
    <label>Headline:</label>
    <input type="text" name="headline" class="form-control"
           value="<?= htmlentities($row['headline']) ?>">
  </div>
  <div class="form-group">
    <label>Summary:</label>
    <textarea name="summary" rows="8" cols="80" class="form-control"><?= htmlentities($row['summary']) ?></textarea>
  </div>

  <p>Position: <input id="addPos" type="button" class="btn btn-default" value="+"></p>
  <div id="position_fields"></div>

  <input type="submit" value="Save" class="btn btn-primary">
  <a href="index.php" class="btn btn-default">Cancel</a>
</form>

<script>
countPos = 0;

// load existing positions from PHP into JS (to render them)
<?php
$jsCount = 0;
foreach ($positions as $pos) {
    $jsCount++;
    $y = htmlentities($pos['year']);
    $d = htmlentities($pos['description']);
    // Escape single quotes for JS
    $d_js = str_replace("'", "\\'", $d);
    echo "countPos = {$jsCount};\n";
    echo "$('#position_fields').append(" .
         "'<div id=\"position{$jsCount}\"> \
            <p>Year: <input type=\"text\" name=\"year{$jsCount}\" value=\"".addslashes($y)."\" /> \
            <input type=\"button\" value=\"-\" onclick=\"\\$('#position{$jsCount}').remove(); return false;\"></p> \
            <textarea name=\"desc{$jsCount}\" rows=\"8\" cols=\"80\">".addslashes($d)."</textarea> \
          </div>');\n";
}
?>

$('#addPos').click(function(event){
    event.preventDefault();
    if (countPos >= 9) {
        alert("Maximum of nine position entries exceeded");
        return;
    }
    countPos++;
    var source = '<div id="position'+countPos+'"> \
      <p>Year: <input type="text" name="year'+countPos+'" /> \
      <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
      <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
    </div>';
    $('#position_fields').append(source);
});
</script>
</body>
</html>
