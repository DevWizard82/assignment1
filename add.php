<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])) die("ACCESS DENIED");

if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
    isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    // Basic profile validation
    if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 ||
        strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
    }
    if (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email address must contain @";
        header("Location: add.php");
        return;
    }

    // Validate positions
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
        header("Location: add.php");
        return;
    }

    // Insert profile
    $stmt = $pdo->prepare('INSERT INTO Profile
        (user_id, first_name, last_name, email, headline, summary)
        VALUES ( :uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    ));
    $profile_id = $pdo->lastInsertId();

    // Insert positions (if any)
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
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ));
        $rank++;
    }

    $_SESSION['success'] = "Profile added";
    header("Location: index.php");
    return;
}
?>
<!doctype html>
<html>
<head>
  <title>anas berrqia - Add Profile</title>
  <link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script
    src="https://code.jquery.com/jquery-3.2.1.js"></script>
</head>
<body class="container">
  <h1>Adding Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<p style="color:green;">'.htmlentities($_SESSION['success'])."</p>\n";
    unset($_SESSION['success']);
}
?>

<form method="post" class="form-horizontal">
  <div class="form-group">
    <label>First Name:</label>
    <input type="text" name="first_name" class="form-control">
  </div>
  <div class="form-group">
    <label>Last Name:</label>
    <input type="text" name="last_name" class="form-control">
  </div>
  <div class="form-group">
    <label>Email:</label>
    <input type="text" name="email" class="form-control">
  </div>
  <div class="form-group">
    <label>Headline:</label>
    <input type="text" name="headline" class="form-control">
  </div>
  <div class="form-group">
    <label>Summary:</label>
    <textarea name="summary" rows="8" cols="80" class="form-control"></textarea>
  </div>

  <p>Position: <input id="addPos" type="button" class="btn btn-default" value="+"></p>
  <div id="position_fields"></div>

  <input type="submit" value="Add" class="btn btn-primary">
  <a href="index.php" class="btn btn-default">Cancel</a>
</form>

<script>
countPos = 0;
// add up to 9
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
