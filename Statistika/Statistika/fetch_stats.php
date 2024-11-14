<?php
// Database connection details
$servername = "localhost";
$db_username = "root"; // Changed variable name to avoid conflict
$password = "";
$dbname = "statistika";

// Create connection
$conn = new mysqli($servername, $db_username, $password, $dbname);
$conn->set_charset("utf8"); // Ensure UTF-8 encoding

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST parameters
$time_period = $_POST['time_period'];
$username = $_POST['username'];

// Initialize variables
$conditions = [];
$params = [];
$types = "";

// Build time range condition
if ($time_period == '24_hours') {
    $conditions[] = "created_at >= ?";
    $params[] = date('Y-m-d H:i:s', strtotime('-1 day'));
    $types .= "s";
} elseif ($time_period == 'week') {
    $conditions[] = "created_at >= ?";
    $params[] = date('Y-m-d H:i:s', strtotime('-1 week'));
    $types .= "s";
} elseif ($time_period == 'month') {
    $conditions[] = "created_at >= ?";
    $params[] = date('Y-m-d H:i:s', strtotime('-1 month'));
    $types .= "s";
} elseif ($time_period == 'custom') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $conditions[] = "created_at BETWEEN ? AND ?";
    $params[] = $start_date . " 00:00:00";
    $params[] = $end_date . " 23:59:59";
    $types .= "ss";
}
// No time condition needed for 'everything'

// Username Filtering
if (!empty($username)) {
    $conditions[] = "username = ?";
    $params[] = $username;
    $types .= "s";
}

// Build the WHERE clause
$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Prepare the main query based on whether username is provided
if (!empty($username)) {
    // If username is provided, fetch the user's entries
    $sql = "SELECT ue.username, t.name AS technology, pt.name AS problem_type, ue.agent, ue.created_at 
            FROM user_entries ue
            JOIN technologies t ON ue.technology_id = t.id
            JOIN problem_types pt ON ue.problem_type_id = pt.id
            $where_clause
            ORDER BY ue.created_at DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Build the table
    if ($result->num_rows > 0) {
        echo "<h2>Korisnik: $username</h2>";
        echo "<table style='margin: 20px auto; width: 80%; border-collapse: collapse;'>";
        echo "<tr><th>Datum</th><th>Tehnologija</th><th>Tip Problema</th><th>Agent</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $date = date('d.m.Y H:i', strtotime($row['created_at']));
            echo "<tr>";
            echo "<td>$date</td>";
            echo "<td>{$row['technology']}</td>";
            echo "<td>{$row['problem_type']}</td>";
            echo "<td>{$row['agent']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nema unosa za korisnika '$username' u odabranom periodu.</p>";
    }
} else {
    // If username is not provided, show the cumulative stats
    // First, get total entries
    $sql_total = "SELECT COUNT(*) as total FROM user_entries $where_clause";
    $stmt_total = $conn->prepare($sql_total);
    if (!empty($params)) {
        $stmt_total->bind_param($types, ...$params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_entries = $row_total['total'];

    if ($total_entries > 0) {
        echo "<h2>Ukupno Unosa: $total_entries</h2>";

        // Summary table for cumulative entries and percentages
        echo "<h2>Suma Unosa po Tehnologijama:</h2>";
        echo "<table style='margin: 20px auto; width: 64%; border-collapse: collapse;'>";
        echo "<tr style='font-weight: bold;'><th>Tehnologije</th><th>Unos</th><th>Procenat</th></tr>";

        // Get entries per technology
        $sql_tech = "SELECT t.name AS technology, COUNT(*) AS count 
                     FROM user_entries ue
                     JOIN technologies t ON ue.technology_id = t.id
                     $where_clause
                     GROUP BY t.name";
        $stmt_tech = $conn->prepare($sql_tech);
        if (!empty($params)) {
            $stmt_tech->bind_param($types, ...$params);
        }
        $stmt_tech->execute();
        $result_tech = $stmt_tech->get_result();

        while ($row = $result_tech->fetch_assoc()) {
            $tech_name = $row['technology'];
            $tech_count = $row['count'];
            $percentage = ($tech_count / $total_entries) * 100;
            echo "<tr><td>$tech_name</td><td>$tech_count</td><td>" . number_format($percentage, 2) . "%</td></tr>";
        }

        echo "</table>";

        // Detailed stats per technology and problem type
        echo "<h3>Pregled Po Tehnologijama</h3>";

        $result_tech->data_seek(0); // Reset result pointer

        while ($row = $result_tech->fetch_assoc()) {
            $tech_name = $row['technology'];
            $tech_count = $row['count'];

            // Display technology header
            echo "<table style='margin: 20px auto; width: 80%; border-collapse: collapse;'>";
            echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
            echo "<td style='width: 50%; text-align: left; padding: 10px;'><u>$tech_name</u></td>";
            echo "<td style='width: 25%; text-align: center; padding: 10px;'>$tech_count</td>";
            echo "<td style='width: 25%; text-align: center; padding: 10px;'></td>";
            echo "</tr>";

            // Get problem types for the current technology
            $sql_problem = "
                SELECT pt.name AS problem_type, COUNT(*) AS problem_count 
                FROM user_entries ue
                JOIN problem_types pt ON ue.problem_type_id = pt.id
                WHERE ue.technology_id = ?";

            // Rebuild conditions excluding technology (already specified)
            $problem_conditions = $conditions;
            $problem_params = $params;
            $problem_types = $types;

            // Remove the technology condition if present
            foreach ($problem_conditions as $key => $condition) {
                if (strpos($condition, 'technology_id =') !== false) {
                    unset($problem_conditions[$key]);
                    unset($problem_params[$key]);
                    $problem_types = str_replace('s', '', $problem_types, $count = 1);
                    break;
                }
            }

            if (!empty($problem_conditions)) {
                $sql_problem .= " AND " . implode(" AND ", $problem_conditions);
            }

            $sql_problem .= " GROUP BY pt.name";

            $stmt_problem = $conn->prepare($sql_problem);

            // Merge parameters
            $problem_params = array_merge([$row['technology']], $problem_params);
            $problem_types = "s" . $problem_types;

            $stmt_problem->bind_param($problem_types, ...$problem_params);
            $stmt_problem->execute();
            $result_problem = $stmt_problem->get_result();

            // Add problem rows
            while ($row_problem = $result_problem->fetch_assoc()) {
                $problem_name = $row_problem['problem_type'];
                $problem_count = $row_problem['problem_count'];
                $problem_percentage = ($problem_count / $tech_count) * 100;

                echo "<tr>";
                echo "<td style='text-align: left; padding: 10px;'>$problem_name</td>";
                echo "<td style='text-align: center; padding: 10px;'>$problem_count</td>";
                echo "<td style='text-align: center; padding: 10px;'>" . number_format($problem_percentage, 2) . "%</td>";
                echo "</tr>";
            }

            echo "</table>";
            $stmt_problem->close();
        }

    } else {
        echo "<p>Nema unosa za odabrani period.</p>";
    }
}

// Close connections
$conn->close();
?>
