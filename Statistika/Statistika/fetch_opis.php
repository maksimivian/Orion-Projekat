<?php
// fetch_opis.php
header('Content-Type: application/json');
require 'db_connection.php';

$level = $_POST['level'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($level === 'technology') {
    $stmt = $conn->prepare("SELECT name, opis FROM technologies WHERE id = ?");
} elseif ($level === 'sub_technology') {
    $stmt = $conn->prepare("SELECT name, opis FROM sub_technologies WHERE id = ?");
} elseif ($level === 'problem_type') {
    $stmt = $conn->prepare("SELECT name, opis FROM problem_types WHERE id = ?");
} elseif ($level === 'problem_location') {
    $stmt = $conn->prepare("SELECT name, opis FROM problem_locations WHERE id = ?");
} else {
    echo json_encode(['error' => 'Invalid level']);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($name, $opis);
$stmt->fetch();
$stmt->close();

echo json_encode(['name' => $name, 'opis' => $opis]);
?>
