<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "keccart";

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Bağlantı Hatası: " . $e->getMessage());
}