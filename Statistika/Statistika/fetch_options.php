<?php
// fetch_options.php
header('Content-Type: application/json');
require 'db_connection.php';

$level = trim($_POST['level'] ?? '');
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$options = [];
$nextLevel = '';

if (strcasecmp($level, 'technology') == 0) {
    // First, check if there are sub-technologies
    $stmt = $conn->prepare("SELECT COUNT(*) FROM sub_technologies WHERE technology_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($subTechCount);
    $stmt->fetch();
    $stmt->close();

    if ($subTechCount > 0) {
        // Fetch sub-technologies
        $stmt = $conn->prepare("SELECT id, name FROM sub_technologies WHERE technology_id = ? ORDER BY name");
        $stmt->bind_param("i", $id);
        $nextLevel = 'sub_technology';
    } else {
        // Fetch problem types directly associated with the technology
        $stmt = $conn->prepare("SELECT id, name FROM problem_types WHERE technology_id = ? AND (sub_technology_id IS NULL OR sub_technology_id = 0) ORDER BY name");
        $stmt->bind_param("i", $id);
        $nextLevel = 'problem_type';
    }
} elseif (strcasecmp($level, 'sub_technology') == 0) {
    $stmt = $conn->prepare("SELECT id, name FROM problem_types WHERE sub_technology_id = ? ORDER BY name");
    $stmt->bind_param("i", $id);
    $nextLevel = 'problem_type';
} elseif (strcasecmp($level, 'problem_type') == 0) {
    $stmt = $conn->prepare("SELECT id, name FROM problem_locations WHERE problem_type_id = ? ORDER BY name");
    $stmt->bind_param("i", $id);
    $nextLevel = 'problem_location';
} else {
    echo json_encode(['error' => 'Invalid level', 'received_level' => $level]);
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
