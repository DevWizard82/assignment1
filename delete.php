<?php
// delete.php
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
$stmt = $pdo->prepare("DELETE FROM Profile WHERE profile_id=:pid");
$stmt->execute(array(':pid'=>$profile_id));

$_SESSION['success'] = "Profile deleted";
header("Location: index.php");
