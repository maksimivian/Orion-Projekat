<?php
// admin/delete_item.php
session_start();

// Check if the agent is logged in and is an admin
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../db_connection.php';

$level = $_GET['level'] ?? '';
$id = $_GET['id'] ?? null;

if (!$id) {
    die("Nevažeći ID.");
}

// Define table based on level
$levels = [
    'technology' => 'technologies',
    'sub_technology' => 'sub_technologies',
    'problem_type' => 'problem_types',
    'problem_location' => 'problem_locations',
];

if (!array_key_exists($level, $levels)) {
    die("Nevažeći nivo.");
}

$table = $levels[$level];

// Delete the entry
$stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header('Location: admin.php');
    exit;
} else {
    die("Greška pri brisanju: " . $stmt->error);
}
?>
