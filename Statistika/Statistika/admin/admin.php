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

// Dohvatimo puno ime agenta iz baze
$query = "SELECT ime FROM agents WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $agent_username);
$stmt->execute();
$stmt->bind_result($agent_name);
$stmt->fetch();
$stmt->close();

$agent_role = $_SESSION['agent_role']; // Retrieve the agent's role

require_once '../header.php';

?>

<!DOCTYPE html>
<html lang="sr">
    
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../CSS/admin_styles.css"> <!-- Uključivanje CSS fajla -->
    <!-- Include Quill CSS and JS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</head>

<body>
    
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

        <!-- Sekcija za agente -->
        <div class="admin-panel">
            <h2>Upravljanje Agentima</h2>
            <a href="add_agent.php" class="admin-button">Dodaj novog agenta</a>
            
            <table>
                <tr>
                    <th>ID</th>
                    <th>Ime</th>
                    <th>Korisničko ime</th>
                    <th>Rola</th>
                    <th>Akcije</th>
                </tr>
                <?php
                $query = "SELECT id, ime, username, role FROM agents ORDER BY username";
                $result = $conn->query($query);
                while ($agent = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($agent['id']); ?></td>
                    <td><?php echo htmlspecialchars($agent['ime']); ?></td>
                    <td><?php echo htmlspecialchars($agent['username']); ?></td>
                    <td><?php echo htmlspecialchars($agent['role']); ?></td>
                    <td>
                        <a href="edit_agent.php?id=<?php echo $agent['id']; ?>">Izmeni</a> |
                        <a href="delete_agent.php?id=<?php echo $agent['id']; ?>" onclick="return confirm('Da li ste sigurni da želite da obrišete ovog agenta?');">Obriši</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div> <!-- End of admin-box -->
</div> <!-- End of container -->

</body>
</html>
