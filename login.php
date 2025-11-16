<?php
session_start();
require_once "pdo.php";

$error = false;
$salt = "XyZzy12*_";

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    $check = hash('md5', $salt.$_POST['pass']);
    $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email=:em AND password=:pw");
    $stmt->execute(array(':em'=>$_POST['email'], ':pw'=>$check));
    $row = $stmt->fetch();

    if ($row !== false) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        header("Location: index.php");
        return;
    } else {
        $_SESSION['error'] = "Incorrect credentials";
        header("Location: login.php");
        return;
    }
}
?>

<html>
<head>
<title>bc52c173</title>
<script>
function doValidate() {
    var pw = document.getElementById('id_1723').value;
    var em = document.getElementById('id_email').value;
    if (em == null || em == "" || pw == null || pw == "") {
        alert("Both fields must be filled out");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<h1>Please log in</h1>

<?php
if (isset($_SESSION['error'])) {
    echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>";
    unset($_SESSION['error']);
}
?>

<form method="post" action="login.php" onsubmit="return doValidate();">
Email: <input type="text" name="email" id="id_email"><br><br>
Password: <input type="password" name="pass" id="id_1723"><br><br>
<input type="submit" value="Log In">
</form>
</body>
</html>
