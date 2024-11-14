<?php
session_start();

// Set session timeout duration (2 hours)
$timeout_duration = 7200; // 2 hours in seconds

// Check for previous activity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Last request was over 2 hours ago, destroy session
    session_unset();
    session_destroy();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Check if the user is already logged in
if (isset($_SESSION['agent_username'])) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Database connection details
    $servername = "localhost";
    $db_username = "root";
    $password = "";
    $dbname = "statistika";

    // Create connection
    $conn = new mysqli($servername, $db_username, $password, $dbname);
    $conn->set_charset("utf8");

    // Check connection
    if ($conn->connect_error) {
        die("Konekcija nije uspela: " . $conn->connect_error);
    }

    // Get submitted username and password
    $username = $_POST['username'];
    $password_input = $_POST['password'];

    // Prepare statement to fetch user
    $stmt = $conn->prepare("SELECT password, role FROM Agents WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if username exists
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($hashed_password, $role);
        $stmt->fetch();

        // Verify password
        if (password_verify($password_input, $hashed_password)) {
            // Password is correct, start a session
            $_SESSION['agent_username'] = $username;
            $_SESSION['agent_role'] = $role; // Store the role in the session
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            header('Location: index.php');
            exit;
        } else {
            // Incorrect password
            $error = "Pogrešno korisničko ime ili lozinka.";
        }
    } else {
        // Username doesn't exist
        $error = "Pogrešno korisničko ime ili lozinka.";
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
<!-- Rest of the HTML code remains the same -->


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Prijava</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body>
    <h1>Prijava</h1>
    <form action="login.php" method="POST" id="loginForm">
        <div class="input-container">
            <label for="username">Korisničko Ime:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-container">
            <label for="password">Lozinka:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <button type="submit">Prijavi se</button>
    </form>
</body>
</html>
