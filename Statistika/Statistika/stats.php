<?php
session_start();
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

include 'header.php';

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Statistika</title>
    <link rel="stylesheet" href="CSS/stats.css">
</head>
<body>

    <h1>Statistika</h1>

    <form id="filterForm">
        <!-- Username Input -->
        <label for="username">Korisničko Ime:</label>
        <input type="text" id="username" name="username">

        <!-- Time Filter -->
        <label for="time_period">Filtriraj po vremenu:</label>
        <select name="time_period" id="time_period" onchange="toggleCustomDateInputs()">
            <option value="24_hours">Poslednjih 24 Časova</option>
            <option value="week">Poslednja Nedelja</option>
            <option value="month">Poslednji Mesec</option>
            <option value="everything">Sve</option>
            <option value="custom">Prilagođeno</option>
        </select>

        <!-- Custom Date Inputs -->
        <div id="customDateInputs" style="display: none;">
            <label for="start_date">Početni Datum:</label>
            <input type="date" id="start_date" name="start_date">
            <label for="end_date">Krajnji Datum:</label>
            <input type="date" id="end_date" name="end_date">
        </div>

        <!-- Search Button -->
        <button type="button" onclick="fetchStats()">Pretraži</button>
    </form>

    <div id="results" style="text-align: center;">
        <!-- Results will be dynamically populated here -->
    </div>

    <script>
        function toggleCustomDateInputs() {
            const timePeriod = document.getElementById('time_period').value;
            const customDateInputs = document.getElementById('customDateInputs');
            if (timePeriod === 'custom') {
                customDateInputs.style.display = 'inline-block';
            } else {
                customDateInputs.style.display = 'none';
            }
        }
    
        function fetchStats() {
            const username = document.getElementById('username').value;
            const timePeriod = document.getElementById('time_period').value;
    
            let params = `time_period=${encodeURIComponent(timePeriod)}&username=${encodeURIComponent(username)}`;
    
            if (timePeriod === 'custom') {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                if (!startDate || !endDate) {
                    alert('Molimo unesite oba datuma za prilagođeni period.');
                    return;
                }
                params += `&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
            }
    
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_stats.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
            xhr.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    const response = this.responseText;
                    document.getElementById('results').innerHTML = response;
                }
            };
    
            xhr.send(params);
        }
    </script>

</body>
</html>
