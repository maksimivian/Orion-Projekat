<?php
session_start();
require '../db_connection.php';

// Proveravamo da li je korisnik prijavljen i da li ima administratorska prava
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

// Prvo, dohvatamo podatke agenta koji se uređuje
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $username = $_POST['username'];
    $new_password = $_POST['password'];
    $role = $_POST['role'];

    // Provera da li je uneta nova šifra
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE agents SET ime = ?, username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $ime, $username, $hashed_password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE agents SET ime = ?, username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $ime, $username, $role, $id);
    }

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Greška prilikom ažuriranja agenta.";
    }
} else {
    // Dohvatamo podatke o agentu iz baze
    $query = "SELECT * FROM agents WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $agent = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Izmeni Agenta</title>
</head>
<body>

<h2>Izmeni Agenta</h2>
<form method="post" action="">
    <label>Ime:</label>
    <input type="text" name="ime" value="<?php echo htmlspecialchars($agent['ime']); ?>" required><br>
    
    <label>Korisničko ime:</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($agent['username']); ?>" required><br>
    
    <label>Nova Lozinka (ostavi prazno ako ne menjaš):</label>
    <input type="password" name="password"><br>
    
    <label>Rola:</label>
    <select name="role">
        <option value="user" <?php if ($agent['role'] === 'user') echo 'selected'; ?>>User</option>
        <option value="admin" <?php if ($agent['role'] === 'admin') echo 'selected'; ?>>Admin</option>
    </select><br>
    
    <input type="submit" value="Izmeni">
</form>

</body>
</html>