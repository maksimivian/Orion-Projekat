<?php
// admin/manage_items.php
session_start();

// Check if the agent is logged in and is an admin
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../db_connection.php';

$level = $_GET['level'] ?? '';
$parent_id = $_GET['parent_id'] ?? null;

// Define table and parent field based on level
$levels = [
    'sub_technology' => ['table' => 'sub_technologies', 'parent_table' => 'technologies', 'parent_field' => 'technology_id'],
    'problem_type' => ['table' => 'problem_types', 'parent_table' => 'sub_technologies', 'parent_field' => 'sub_technology_id'],
    'problem_location' => ['table' => 'problem_locations', 'parent_table' => 'problem_types', 'parent_field' => 'problem_type_id'],
    // Add more levels as needed
];

if (!array_key_exists($level, $levels)) {
    die("Nevažeći nivo.");
}

$table = $levels[$level]['table'];
$parent_table = $levels[$level]['parent_table'];
$parent_field = $levels[$level]['parent_field'];

// Fetch parent name
$stmt = $conn->prepare("SELECT name FROM $parent_table WHERE id = ?");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$stmt->bind_result($parent_name);
$stmt->fetch();
$stmt->close();

// Fetch entries
$stmt = $conn->prepare("SELECT id, name FROM $table WHERE $parent_field = ? ORDER BY name");
$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
// Dohvatimo puno ime agenta iz baze
$query = "SELECT ime FROM agents WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $agent_username);
$stmt->execute();
$stmt->bind_result($agent_name);
$stmt->fetch();
$stmt->close();


include '../header.php';
?>


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Upravljanje <?php echo ucfirst(str_replace('_', ' ', $level)); ?></title>
    <link rel="stylesheet" href="../CSS/admin_styles.css">
</head>
<body>


<div class="container">
    <h1><?php echo ucfirst(str_replace('_', ' ', $level)); ?> za <?php echo htmlspecialchars($parent_name); ?></h1>
    <a href="edit_item.php?level=<?php echo $level; ?>&action=add&parent_id=<?php echo $parent_id; ?>" class="admin-button">Dodaj novi</a>
    <table>
        <tr>
            <th>Naziv</th>
            <th>Akcije</th>
        </tr>
        <?php while ($item = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td>
                <a href="edit_item.php?level=<?php echo $level; ?>&action=edit&id=<?php echo $item['id']; ?>&parent_id=<?php echo $parent_id; ?>">Izmeni</a> |
                <?php if ($level !== 'problem_location'): ?>
                    <?php
                    // Determine the next level
                    $next_level = '';
                    if ($level === 'sub_technology') $next_level = 'problem_type';
                    if ($level === 'problem_type') $next_level = 'problem_location';
                    ?>
                    <a href="manage_items.php?level=<?php echo $next_level; ?>&parent_id=<?php echo $item['id']; ?>">Upravljaj <?php echo ucfirst(str_replace('_', ' ', $next_level)); ?></a> |
                <?php endif; ?>
                <a href="delete_item.php?level=<?php echo $level; ?>&id=<?php echo $item['id']; ?>" onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj unos?');">Obriši</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <a href="admin.php">Nazad na Admin Dashboard</a>
</div>

</body>
</html>
