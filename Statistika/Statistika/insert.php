<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_connection.php';

    // Retrieve form data and sanitize
    $username = $_POST['username'] ?? '';
    $technology_id = $_POST['technology'] ?? '';
    $sub_technology_id = !empty($_POST['sub_technology']) ? (int)$_POST['sub_technology'] : null;
    $problem_type_id = !empty($_POST['problem_type']) ? (int)$_POST['problem_type'] : null;
    $problem_location_id = !empty($_POST['problem_location']) ? (int)$_POST['problem_location'] : null;
    $agent_username = $_SESSION['agent_username'];

    // Initialize an array to collect errors
    $errors = [];

    // Check required fields
    if (empty($username)) {
        $errors[] = 'Korisničko ime je obavezno.';
    }
    if (empty($technology_id)) {
        $errors[] = 'Tehnologija je obavezna.';
    }

    // Additional checks based on available options
    if (isset($_POST['sub_technology_required']) && $_POST['sub_technology_required'] === '1' && empty($sub_technology_id)) {
        $errors[] = 'Sub tehnologija je obavezna.';
    }
    if (isset($_POST['problem_type_required']) && $_POST['problem_type_required'] === '1' && empty($problem_type_id)) {
        $errors[] = 'Tip problema je obavezan.';
    }
    if (isset($_POST['problem_location_required']) && $_POST['problem_location_required'] === '1' && empty($problem_location_id)) {
        $errors[] = 'Lokacija problema je obavezna.';
    }

    if (empty($errors)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO user_entries (
            username, agent, technology_id, sub_technology_id, problem_type_id, problem_location_id
        ) VALUES (?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "ssiiii",
            $username,
            $agent_username,
            $technology_id,
            $sub_technology_id,
            $problem_type_id,
            $problem_location_id
        );

        // Execute and check for errors
        if ($stmt->execute()) {
            header('Location: index.php?success=1');
            exit;
        } else {
            echo "Greška pri čuvanju unosa: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Display errors
        foreach ($errors as $error) {
            echo "<p>$error</p>";
        }
    }

    $conn->close();
} else {
    echo "Nevažeći zahtev.";
}
?>
