<?php
$host = "HOST-ADRESS";
$name = "DATABASE-NAME";
$user = "USERNAME";
$passwort = "PASSWORD";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $passwort, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    echo "SQL Error: " . $e->getMessage();
}
?>
