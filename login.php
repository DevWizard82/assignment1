<?php
session_start();
require_once "pdo.php";

$salt = "XyZzy12*_";

// Handle POST submission
if (isset($_POST['email']) && isset($_POST['pass'])) {

    // Validate password
    $check = hash('md5', $salt . $_POST['pass']);
    $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email=:em AND password=:pw");
    $stmt->execute(array(':em'=>$_POST['email'], ':pw'=>$check));
    $row = $stmt->fetch();

    if ($row !== false) {
        // Successful login
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['name'] = $row['name'];
        header("Location: index.php");
        exit();
    } else {
        // Failed login
        $_SESSION['error'] = "Incorrect credentials";
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Anas Berrqia - Login</title>
    <script>
    function doValidate() {
        var pw = document.getElementById('id_1723').value;
        var em = document.getElementById('id_email').value;
        if (em === "" || pw === "") {
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
        echo '<p style="color:red;">'.htmlentities($_SESSION['error'])."</p>\n";
        unset($_SESSION['error']);
    }
    ?>

    <form method="post" action="login.php" onsubmit="return doValidate();">
        <p>Email: <input type="text" name="email" id="id_email"></p>
        <p>Password: <input type="password" name="pass" id="id_1723"></p>
        <p><input type="submit" value="Log In"></p>
    </form>

    <p><a href="add.php">Add New Entry</a></p>
</body>
</html>
