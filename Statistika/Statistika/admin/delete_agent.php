<?php
session_start();
require '../db_connection.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM agents WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: admin.php");
    exit();
} else {
    echo "GreÅ¡ka prilikom brisanja agenta.";
}
?>