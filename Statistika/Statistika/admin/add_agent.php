<?php
session_start();
require '../db_connection.php';

// Proveravamo da li je korisnik prijavljen i da li ima administratorska prava
if (!isset($_SESSION['agent_username']) || $_SESSION['agent_role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ime = $_POST['ime'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO agents (ime, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ime, $username, $hashed_password, $role);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "Greška prilikom dodavanja agenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Dodaj Novog Agenta</title>
</head>
<body>

<h2>Dodaj Novog Agenta</h2>
<form method="post" action="">
    <label>Ime:</label>
    <input type="text" name="ime" required><br>
    
    <label>Korisničko ime:</label>
    <input type="text" name="username" required><br>
    
    <label>Lozinka:</label>
    <input type="password" name="password" required><br>
    
    <label>Rola:</label>
    <select name="role">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br>
    
    <input type="submit" value="Dodaj">
</form>

</body>
</html>