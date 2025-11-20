<?php
session_start();
require_once "pdo.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    return;
}

// Handle POST submission
if (isset($_POST['first_name']) && isset($_POST['last_name']) &&
    isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

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
            if (!isset($_POST['year'.$i]) || !isset($_POST['desc'.$i])) continue;
            $year = trim($_POST['year'.$i]);
            $desc = trim($_POST['desc'.$i]);
            if (strlen($year)==0 || strlen($desc)==0) return "All fields are required";
            if (!is_numeric($year)) return "Position year must be numeric";
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
        VALUES (:uid, :fn, :ln, :em, :he, :su)');
    $stmt->execute([
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']
    ]);
    $profile_id = $pdo->lastInsertId();

    // Insert positions
    $rank = 1;
    for ($i=1; $i<=9; $i++) {
        if (!isset($_POST['year'.$i]) || !isset($_POST['desc'.$i])) continue;
        $stmt = $pdo->prepare('INSERT INTO Position
            (profile_id, rank, year, description)
            VALUES (:pid, :rank, :year, :desc)');
        $stmt->execute([
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $_POST['year'.$i],
            ':desc' => $_POST['desc'.$i]
        ]);
        $rank++;
    }

    $_SESSION['success'] = "Profile added";
    header("Location: index.php");
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Anas Berrqia - Add Profile</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
</head>
<body>
<div class="container">
<h1>Adding Profile</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="post">
<p>First Name: <input type="text" name="first_name"></p>
<p>Last Name: <input type="text" name="last_name"></p>
<p>Email: <input type="text" name="email"></p>
<p>Headline:<br/><input type="text" name="headline"></p>
<p>Summary:<br/><textarea name="summary" rows="8" cols="80"></textarea></p>

<p>Position: <input type="button" id="addPos" value="+"></p>
<div id="position_fields"></div>

<p>
<input type="submit" name="add" value="Add">
<a href="index.php">Cancel</a>
</p>
</form>

<script>
countPos = 0;
$('#addPos').click(function(event){
    event.preventDefault();
    if (countPos >= 9) {
        alert("Maximum of nine position entries exceeded");
        return;
    }
    countPos++;
    $('#position_fields').append(
        '<div id="position'+countPos+'">\
        <p>Year: <input type="text" name="year'+countPos+'"> \
        <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove(); return false;"></p>\
        <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
        </div>'
    );
});
</script>
</div>
</body>
</html>
