<?php
session_start();
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

include 'header.php';

?>


<!DOCTYPE html>
<html lang="sr">

<head>
    <meta charset="UTF-8">
    <title>Tech Issues Submission</title>
    <link rel="stylesheet" href="CSS/styles.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>

<body>

    <div id="successMessage" class="success-message">Novi unos je uspešno sačuvan</div>

    <div class="container">
        <div class="left-side">
            <form action="insert.php" method="POST" id="issueForm">
                <!-- Username Input -->
                <div class="input-container">
                    <label for="username">Korisničko Ime:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <!-- Technology Buttons -->
                <div class="tech-buttons">
                    <?php
                    // Fetch technologies from the database
                    require 'db_connection.php';
                    $query = "SELECT id, name FROM technologies ORDER BY name";
                    $result = $conn->query($query);
                    while ($tech = $result->fetch_assoc()):
                    ?>
                        <button type="button" class="tech-btn" data-id="<?php echo $tech['id']; ?>">
                            <?php echo htmlspecialchars($tech['name']); ?>
                        </button>
                    <?php endwhile; ?>
                </div>

                <!-- Dynamic Buttons for Subsequent Levels -->
                <div id="sub-tech-buttons" class="sub-tech-buttons"></div>
                <div id="problem-type-buttons" class="problem-type-buttons"></div>
                <div id="problem-location-buttons" class="problem-location-buttons"></div>
                <div id="problem-buttons" class="problem-buttons"></div>

                <!-- Hidden Inputs to indicate required fields -->
                <input type="hidden" id="sub_technology_required" name="sub_technology_required" value="">
                <input type="hidden" id="problem_type_required" name="problem_type_required" value="">
                <input type="hidden" id="problem_location_required" name="problem_location_required" value="">


                <!-- Hidden Inputs to send selected data -->
                <input type="hidden" id="technology" name="technology" required>
                <input type="hidden" id="sub_technology" name="sub_technology">
                <input type="hidden" id="problem_type" name="problem_type">
                <input type="hidden" id="problem_location" name="problem_location">
                <input type="hidden" name="agent" value="<?php echo htmlspecialchars($agent_username); ?>">

                <!-- Submit Button -->
                <div class="submit-container">
                    <button type="submit" id="submitBtn" disabled>Unesi</button>
                </div>
            </form>
        </div>

        
        <!--<div class="right-side">
            <h2 class="manual-title">Informacije</h2>
            <div id="selected-info" class="manual-content">
             Technology and Problem Info will appear here 
            </div>
                                <div id="selected-info"></div>  
        </div> Ostavljam za rezervu dok testiram ovo ispod-->

        <!-- Right Side (Descriptions and Selected Info) -->
        <div class="right-side">
            <h2 id="opis-title"></h2>
            <div id="opis-content"></div>
 
        </div>

    </div>

    <!-- Include script.js -->
    <script src="script.js"></script>

 <!-- Success Message Script -->
<script>
    // Check if the URL has a success parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        const successMessage = document.getElementById('successMessage');
        successMessage.style.display = 'block'; // Show the message

        // Hide the message after 2 seconds
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 2000);
    }
</script>


</body>
</html>
