<?php
// fetch_options.php
header('Content-Type: application/json');
require 'db_connection.php';

$level = $_POST['level'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$options = [];
$nextLevel = '';

if ($level === 'technology') {
    $stmt = $conn->prepare("SELECT id, name FROM sub_technologies WHERE technology_id = ? ORDER BY name");
    $stmt->bind_param("i", $id);
    $nextLevel = 'sub_technology';
} elseif ($level === 'sub_technology') {
    $stmt = $conn->prepare("SELECT id, name FROM problem_types WHERE sub_technology_id = ? ORDER BY name");
    $stmt->bind_param("i", $id);
    $nextLevel = 'problem_type';
} elseif ($level === 'problem_type') {
    $stmt = $conn->prepare("SELECT id, name FROM problem_locations WHERE problem_type_id = ? ORDER BY name");
    $stmt->bind_param("i", $id);
    $nextLevel = 'problem_location';
} else {
    echo json_encode(['error' => 'Invalid level']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $options[] = ['id' => $row['id'], 'name' => $row['name']];
}
$stmt->close();

echo json_encode([
    'level' => $nextLevel,
    'options' => $options
]);

$conn->close();
?>
