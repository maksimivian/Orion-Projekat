<?php


// Include the database connection
require 'db_connection.php';

// Session timeout logic
$timeout_duration = 7200; // 2 hours in seconds
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Check if the agent is logged in
if (!isset($_SESSION['agent_username'])) {
    header('Location: login.php');
    exit;
}
$agent_username = $_SESSION['agent_username'];
$agent_role = $_SESSION['agent_role']; // Retrieve the agent's role
// Dohvatimo puno ime agenta iz baze
$query = "SELECT ime FROM agents WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $agent_username);
$stmt->execute();
$stmt->bind_result($agent_name);
$stmt->fetch();
$stmt->close();
?>

<header class="sticky-header">
    <div class="nav-left">
        <a href="/statistika/index.php" class="nav-button">Unos</a>
        <a href="/statistika/stats.php" class="nav-button">Statistika</a>
        <a href="/statistika/orionteka/orionteka.php" class="nav-button">Orionteka</a>
        <?php if ($agent_role === 'admin'): ?>
            <a href="/statistika/admin/admin.php" class="nav-button">Admin</a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <span class="agent-name"><?php echo htmlspecialchars($agent_name); ?></span>
        <a href="/statistika/logout.php" class="nav-button">Odjavi se</a>
    </div>
</header>
