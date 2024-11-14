<?php
session_start();
include('../db_connection.php'); // Uključi konekciju iz ../db_connection.php

// Dohvatanje imena agenta
$agent_name = "";
if (isset($_SESSION['agent_username'])) {
    $agent_username = $_SESSION['agent_username'];

    // Dohvati puno ime agenta iz baze
    $sql = "SELECT ime FROM agents WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $agent_username);
    $stmt->execute();
    $stmt->bind_result($agent_name);
    $stmt->fetch();
    $stmt->close();
}

// Provera da li je tehnologija odabrana
$tech = isset($_GET['tech']) ? $_GET['tech'] : null;
$description = "";

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

include '../header.php';
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Orionteka</title>
    <link rel="stylesheet" href="../CSS/orionteka.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <!-- Uključivanje Quill biblioteke -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <!-- Dodajte CSS za fiksnu širinu editora i automatsku visinu -->
    <style>
        #editor-container {
            width: 1500px; /* Postavite željenu širinu u pikselima */
            max-width: 150%; /* Opciono, omogućava da se širina prilagodi maksimalnoj širini roditeljskog elementa */
            min-height: 650px; /* Minimalna visina editora */
            height: auto; /* Visina će rasti automatski sa sadržajem */
            max-height: 650px; /* Opciono: postavite maksimalnu visinu ako želite da se spreči prekomerno proširivanje vertikalno */
            overflow-y: auto; /* Omogućava vertikalno skrolovanje ako sadržaj premaši visinu */
            box-sizing: border-box; /* Uključuje padding u ukupnu širinu/visinu */
        }
        .ql-editor {
            overflow-y: hidden;
        }

    </style>
</head>
<body>

   

    <div class="container">
        <div class="sidebar">
            <!-- DSL kao osnovna tehnologija sa mogućnošću proširenja -->
            <a href="javascript:void(0);" class="sidebar-item" onclick="toggleDSL()">
              <i class="fas fa-chevron-down"></i> DSL
            </a>

            <div id="dsl-collapse" class="collapse">
                <a href="?tech=ADSL" class="sidebar-itemx <?= $tech == 'ADSL' ? 'active' : '' ?>">ADSL</a>
                <a href="?tech=VDSL" class="sidebar-itemx <?= $tech == 'VDSL' ? 'active' : '' ?>">VDSL</a>
                <a href="?tech=D@H" class="sidebar-itemx <?= $tech == 'D@H' ? 'active' : '' ?>">D@H</a>
            </div>

            <a href="?tech=WiFi" class="sidebar-item <?= $tech == 'WiFi' ? 'active' : '' ?>">WiFi</a>
            <a href="?tech=Glight" class="sidebar-item <?= $tech == 'Glight' ? 'active' : '' ?>">Glight</a>
            <a href="?tech=GPON" class="sidebar-item <?= $tech == 'GPON' ? 'active' : '' ?>">GPON</a>
            <a href="?tech=WiFi6E" class="sidebar-item <?= $tech == 'WiFi6E' ? 'active' : '' ?>">WiFi6E</a>
            <a href="?tech=IPTV" class="sidebar-item <?= $tech == 'IPTV' ? 'active' : '' ?>">IPTV</a>
        </div>

        <!-- Glavni sadržaj -->
        <div class="main-content">
            <?php if ($tech): ?>
                <h3>Opis za <?= ucfirst($tech) ?></h3>
                <?php if ($_SESSION['agent_role'] == 'admin'): ?>
                    <!-- Form za unos opisa (samo za admina) -->
                    <form action="orionteka.php?tech=<?= urlencode($tech) ?>" method="POST">
                        <div id="editor-container"><?= htmlspecialchars($description) ?></div>
                        <input type="hidden" name="description" id="description">
                        <button type="submit" name="save_description">Spremi</button>
                    </form>
                <?php else: ?>
                    <!-- Prikaz opisa za korisnike koji nisu admini -->
                    <textarea rows="6" readonly><?= htmlspecialchars($description) ?></textarea>
                <?php endif; ?>
            <?php else: ?>
                <h3>Izaberite tehnologiju sa leve strane</h3>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript za omogućavanje proširivanja DSL menija -->
    <script>
        function toggleDSL() {
            var collapseElement = document.getElementById("dsl-collapse");
            if (collapseElement.classList.contains("collapse")) {
                collapseElement.classList.remove("collapse");
            } else {
                collapseElement.classList.add("collapse");
            }
        }

        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': '1' }, { 'header': '2' }, { 'font': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    ['image']
                ]
            }
        });

        var form = document.querySelector('form');
        form.onsubmit = function() {
            var description = document.querySelector('#description');
            description.value = quill.root.innerHTML;
        };
    </script>

</body>
</html>