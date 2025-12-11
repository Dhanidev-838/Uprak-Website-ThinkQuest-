<?php
// config.php

// Database configuration
$host = "localhost";        // biasanya localhost
$db_name = "thinkquest";    // nama database
$username = "root";         // username database
$password = "";             // password database

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset ke utf8mb4 untuk mendukung emoji dan karakter spesial
$conn->set_charset("utf8mb4");
?>
