<?php
// admin/edit_item.php
session_start();

// Check if the agent is logged in and is an admin
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require '../db_connection.php';

$level = $_GET['level'] ?? '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$parent_id = $_GET['parent_id'] ?? null;

// Define table and parent field based on level
$levels = [
    'technology' => ['table' => 'technologies', 'parent_field' => null],
    'sub_technology' => ['table' => 'sub_technologies', 'parent_field' => 'technology_id'],
    'problem_type' => ['table' => 'problem_types', 'parent_field' => 'sub_technology_id'],
    'problem_location' => ['table' => 'problem_locations', 'parent_field' => 'problem_type_id'],
];

if (!array_key_exists($level, $levels)) {
    die("Nevažeći nivo.");
}

$table = $levels[$level]['table'];
$parent_field = $levels[$level]['parent_field'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $opis = $_POST['opis'];

    if ($action === 'add') {
        if ($parent_field) {
            $stmt = $conn->prepare("INSERT INTO $table ($parent_field, name, opis) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $parent_id, $name, $opis);
        } else {
            $stmt = $conn->prepare("INSERT INTO $table (name, opis) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $opis);
        }
    } elseif ($action === 'edit' && $id) {
        $stmt = $conn->prepare("UPDATE $table SET name = ?, opis = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $opis, $id);
    } else {
        die("Nevažeća akcija.");
    }

    if ($stmt->execute()) {
        header('Location: ' . ($parent_field ? "manage_items.php?level=$level&parent_id=$parent_id" : 'admin.php'));
        exit;
    } else {
        $error = "Greška: " . $stmt->error;
    }
} else {
    $name = '';
    $opis = '';

    if ($action === 'edit' && $id) {
        $stmt = $conn->prepare("SELECT name, opis FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($name, $opis);
        $stmt->fetch();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $action === 'add' ? 'Dodaj' : 'Izmeni'; ?> <?php echo ucfirst(str_replace('_', ' ', $level)); ?></title>
    <link rel="stylesheet" href="../CSS/admin_styles.css">
</head>
<body>

<header class="sticky-header">
    <div class="nav-left">
        <a href="../index.php" class="nav-button">Unos</a>
        <a href="../stats.php" class="nav-button">Statistika</a>
        <a href="admin.php" class="nav-button">Admin</a>
    </div>
    <div class="nav-right">
        <span class="agent-name"><?php echo htmlspecialchars($_SESSION['agent_username']); ?></span>
        <a href="../logout.php" class="nav-button">Odjavi se</a>
    </div>
</header>

<div class="container">
    <h1><?php echo $action === 'add' ? 'Dodaj' : 'Izmeni'; ?> <?php echo ucfirst(str_replace('_', ' ', $level)); ?></h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <label for="name">Naziv:</label><br>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required><br><br>
        <label for="opis">Opis:</label><br>
        <textarea name="opis" id="opis" rows="5" cols="50"><?php echo htmlspecialchars($opis); ?></textarea><br><br>
        <button type="submit">Sačuvaj</button>
        <a href="<?php echo $parent_field ? "manage_items.php?level=$level&parent_id=$parent_id" : 'admin.php'; ?>">Otkaži</a>
    </form>
</div>

</body>
</html>
