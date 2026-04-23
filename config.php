<?php
$host = 'localhost';
$dbname = 'web_kamsis';
$username = 'root'; // Sesuaikan dengan username database kamu
$password = '';     // Sesuaikan dengan password database kamu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Mengatur mode error PDO menjadi Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>