<?php
session_start();
include('../db_connection.php'); // Uključi konekciju iz ../db_connection.php

// Provera da li je tehnologija odabrana
$tech = isset($_GET['tech']) ? $_GET['tech'] : null;
$description = "";

// Provera da li su varijable za ulogu i korisničko ime postavljene
$agent_role = isset($_SESSION['agent_role']) ? $_SESSION['agent_role'] : null;
$agent_username = isset($_SESSION['agent_username']) ? $_SESSION['agent_username'] : null;


// Ako je tehnologija odabrana, učitaj njen opis iz baze
if ($tech) {
    $sql = "SELECT description FROM orionteka WHERE tech = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tech);
    $stmt->execute();
    $stmt->bind_result($description);
    $stmt->fetch();
    $stmt->close();
}

// Obrada unosa teksta u bazi (samo za admina)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_description']) && $tech) {
    // Proveri da li je korisnik admin
    if ($_SESSION['agent_role'] == 'admin') {
        $new_description = $_POST['description'];

        // Provera da li već postoji unos za ovu tehnologiju
        $sql = "SELECT id FROM orionteka WHERE tech = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tech);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Ažuriraj postojeći unos
            $sql = "UPDATE orionteka SET description = ? WHERE tech = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $new_description, $tech);
            $stmt->execute();
            $stmt->close();
        } else {
            // Unesi novi unos
            $sql = "INSERT INTO orionteka (tech, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $tech, $new_description);
            $stmt->execute();
            $stmt->close();
        }

        // Osvježavanje stranice da bi se prikazao novi tekst
        header("Location: orionteka.php?tech=" . urlencode($tech));
        exit(); // Obavezno pozvati exit() nakon header() kako bi skripta prestala s izvršavanjem
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orionteka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet"> <!-- Font Awesome za strelice -->
    <style>
        /* Dodajemo CSS za fiksiran i proširujući box */
        .description-box {
            width: 100%; /* Box će zauzeti punu širinu dostupnog prostora */
            max-width: 100%; /* Obezbeđuje da box ne izlazi izvan granica */
            height: auto; /* Visina boxa raste sa tekstom */
            min-height: 100px; /* Minimalna visina za box */
            resize: none; /* Onemogućava korisnicima da menjaju veličinu box-a */
            overflow-y: auto; /* Omogućava skrolovanje u vertikalnom smeru ako je tekst predugačak */
            padding: 10px;
            font-size: 14px;
            line-height: 1.5;
            border: 1px solid #ccc; /* Dodavanje ivice */
            border-radius: 5px;
            background-color: #f9f9f9; /* Boja pozadine */
        }
    </style>
</head>
<body>

<!-- Header (istog tipa kao na index i admin stranici) -->
<header class="sticky-header">
        <div class="nav-left">
            <a href="../index.php" class="nav-button">Unos</a>
            <a href="../stats.php" class="nav-button">Statistika</a>
            <a href="orionteka/orionteka.php" class="nav-button">Orionteka</a>
            <?php if ($agent_role === 'admin'): ?>
                <a href="../admin/admin.php" class="nav-button">Admin</a>
            <?php endif; ?>
        </div>
        <div class="nav-right">
            <span class="agent-name"><?php echo htmlspecialchars($agent_username); ?></span>
            <a href="../logout.php" class="nav-button">Odjavi se</a>
        </div>
</header>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar (dropdown sa tehnologijama) -->
        <div class="col-md-3">
            <div class="list-group">
                <!-- DSL kao osnovna tehnologija sa mogućnošću proširenja -->
                <a href="#dsl-collapse" class="list-group-item list-group-item-action" data-toggle="collapse" aria-expanded="false" aria-controls="dsl-collapse">
                    <i class="fas fa-chevron-down"></i> DSL
                </a>

                <!-- Collapse za podtehnologije ADSL, VDSL, D@H -->
                <div id="dsl-collapse" class="collapse <?= $tech == 'ADSL' || $tech == 'VDSL' || $tech == 'D@H' ? 'show' : '' ?>">
                    <a href="orionteka.php?tech=ADSL" class="list-group-item list-group-item-action <?= $tech == 'ADSL' ? 'active' : '' ?>">ADSL</a>
                    <a href="orionteka.php?tech=VDSL" class="list-group-item list-group-item-action <?= $tech == 'VDSL' ? 'active' : '' ?>">VDSL</a>
                    <a href="orionteka.php?tech=D@H" class="list-group-item list-group-item-action <?= $tech == 'D@H' ? 'active' : '' ?>">D@H</a>
                </div>

                <!-- Ostale tehnologije -->
                <a href="orionteka.php?tech=WiFi" class="list-group-item list-group-item-action <?= $tech == 'WiFi' ? 'active' : '' ?>">WiFi</a>
                <a href="orionteka.php?tech=Glight" class="list-group-item list-group-item-action <?= $tech == 'Glight' ? 'active' : '' ?>">Glight</a>
                <a href="orionteka.php?tech=GPON" class="list-group-item list-group-item-action <?= $tech == 'GPON' ? 'active' : '' ?>">GPON</a>
                <a href="orionteka.php?tech=WiFi6E" class="list-group-item list-group-item-action <?= $tech == 'WiFi6E' ? 'active' : '' ?>">WiFi6E</a>
                <a href="orionteka.php?tech=IPTV" class="list-group-item list-group-item-action <?= $tech == 'IPTV' ? 'active' : '' ?>">IPTV</a>
            </div>
        </div>

        <!-- Glavni sadržaj sa box-om za unos teksta -->
        <div class="col-md-9">
            <?php if ($tech): ?>
                <h3>Opis za <?= ucfirst($tech) ?></h3>

                <?php if ($_SESSION['agent_role'] == 'admin'): ?>
                    <!-- Form for editing description (visible only for admin) -->
                    <form action="orionteka.php?tech=<?= urlencode($tech) ?>" method="POST">
                        <div class="form-group">
                            <label for="description">Unesite opis tehnologije</label>
                            <textarea class="form-control description-box" id="description" name="description" rows="6"><?= htmlspecialchars($description) ?></textarea>
                        </div>
                        <button type="submit" name="save_description" class="btn btn-primary">Sacuvaj</button>
                    </form>
                <?php else: ?>
                    <!-- Display description for non-admin users -->
                    <div class="form-group">
                        <label>Opis tehnologije</label>
                        <textarea class="form-control description-box" rows="6" readonly><?= htmlspecialchars($description) ?></textarea>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h3>Izaberite tehnologiju sa leve strane</h3>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>