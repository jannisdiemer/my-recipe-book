<?php
$host = "localhost:3306";
$name = "recepies";
$user = "access";
$passwort = "WN8Bx~xuse$6ydm8";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $passwort, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "SQL Error: " . $e->getMessage();
}
?>
