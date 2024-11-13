<?php
// admin/admin.php
session_start();

// Include the database connection
require '../db_connection.php';

// Session timeout logic (optional)
$timeout_duration = 7200; // 2 hours in seconds
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Check if the agent is logged in and is an admin
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$agent_username = $_SESSION['agent_username'];
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/admin_styles.css"> <!-- Uključivanje CSS fajla -->
</head>
<body>

<!-- Header section (identical to the one in index.php) -->
<header class="sticky-header">
    <div class="nav-left">
        <a href="../index.php" class="nav-button">Unos</a>
        <a href="../stats.php" class="nav-button">Statistika</a>
        <?php if ($_SESSION['agent_role'] === 'admin'): ?>
            <a href="admin.php" class="nav-button">Admin</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <span class="agent-name"><?php echo htmlspecialchars($agent_username); ?></span>
        <a href="../logout.php" class="nav-button">Odjavi se</a>
    </div>
</header>

<!-- Admin Dashboard Content -->
<div class="container">
    <div class="admin-box">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-panel">
            <h2>Tehnologije</h2>
            <a href="edit_item.php?level=technology&action=add" class="admin-button">Dodaj novu tehnologiju</a>

            <table>
                <tr>
                    <th>Naziv</th>
                    <th>Akcije</th>
                </tr>
                <?php
                $query = "SELECT id, name FROM technologies ORDER BY name";
                $result = $conn->query($query);
                while ($tech = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($tech['name']); ?></td>
                    <td>
                        <a href="edit_item.php?level=technology&action=edit&id=<?php echo $tech['id']; ?>">Izmeni</a> |
                        <a href="manage_items.php?level=sub_technology&parent_id=<?php echo $tech['id']; ?>">Sub Tehnologije</a> |
                        <a href="delete_item.php?level=technology&id=<?php echo $tech['id']; ?>" onclick="return confirm('Da li ste sigurni da želite da obrišete ovu tehnologiju?');">Obriši</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div> <!-- End of admin-panel -->
    </div> <!-- End of admin-box -->
</div> <!-- End of container -->

</body>
</html>
