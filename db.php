<?php
$host = 'localhost';
$dbname = 'utm_generator';
$username = 'root'; // Ваше имя пользователя MySQL
$password = '';     // Ваш пароль MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}