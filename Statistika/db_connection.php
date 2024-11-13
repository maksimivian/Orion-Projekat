<?php
// db_connection.php

// Database connection details
$servername = "localhost";
$db_username = "root";
$password = "";
$dbname = "statistika";

// Create a new MySQLi connection
$conn = new mysqli($servername, $db_username, $password, $dbname);

// Set the character set to UTF-8 (optional but recommended)
$conn->set_charset("utf8mb4");

// Check the connection
if ($conn->connect_error) {
    die("Konekcija nije uspela: " . $conn->connect_error);
}
?>
