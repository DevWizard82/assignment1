<?php
// pdo.php
$host = 'localhost';
$port = 3306;  // change this if your MySQL uses a different port
$db   = 'misc';
$user = 'fred';
$pass = 'zap';
$charset = 'utf8';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     echo "Connection failed: " . $e->getMessage();
     exit();
}
