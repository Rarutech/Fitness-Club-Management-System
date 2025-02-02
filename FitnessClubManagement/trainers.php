<?php
include "database.php";  // Database connection

$method = $_SERVER['REQUEST_METHOD'];
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

// Parse input based on content type
if ($contentType === "application/json") {
    $input = json_decode(file_get_contents('php://input'), true);
} elseif ($contentType === "application/x-www-form-urlencoded" || $contentType === "multipart/form-data") {
    $input = $_POST;
} else {
    parse_str(file_get_contents('php://input'), $input);
}

// Respond function for JSON responses
function respond($message, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode(["status" => $status == 200 ? "success" : "error", "message" => $message]);
    exit();
}

### POST Method - Create Trainer ###
if ($method == "POST") {
    if (empty($input["user_id"]) || empty($input["specialty"]) || !isset($input["experience_years"])) {
        respond("User ID, specialty, and experience years are required", 400);
    }

    $user_id = $input["user_id"];
    $specialty = $input["specialty"];
    $experience_years = $input["experience_years"];

    // Check if user is already a trainer
    $stmt = $conn->prepare("SELECT id FROM trainers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    if ($stmt->fetch()) {
        respond("Error: This user is already a trainer", 400);
    }
    $stmt->close();

    // Insert new trainer
    $stmt = $conn->prepare("INSERT INTO trainers (user_id, specialty, experience_years) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $specialty, $experience_years);

    if ($stmt->execute()) {
        respond("Trainer created successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

### GET Method - Fetch Trainers ###
elseif ($method == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $stmt = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            respond($result->fetch_assoc());
        } else {
            respond("Trainer not found", 404);
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM trainers");
        $trainers = $result->fetch_all(MYSQLI_ASSOC);
        respond($trainers);
    }
}

### PUT Method - Update Trainer ###
elseif ($method == "PUT") {
    if (empty($input["id"]) || empty($input["specialty"]) || !isset($input["experience_years"])) {
        respond("Trainer ID, specialty, and experience years are required", 400);
    }

    $id = $input["id"];
    $specialty = $input["specialty"];
    $experience_years = $input["experience_years"];

    // Check if trainer exists
    $stmt = $conn->prepare("SELECT id FROM trainers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->fetch()) {
        respond("Error: Trainer with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Update trainer info
    $stmt = $conn->prepare("UPDATE trainers SET specialty=?, experience_years=? WHERE id=?");
    $stmt->bind_param("sii", $specialty, $experience_years, $id);

    if ($stmt->execute()) {
        respond("Trainer updated successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

### DELETE Method - Remove Trainer ###
elseif ($method == "DELETE") {
    if (empty($input["id"])) {
        respond("Trainer ID is required", 400);
    }

    $id = $input["id"];

    // Check if trainer exists
    $stmt = $conn->prepare("SELECT id FROM trainers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->fetch()) {
        respond("Error: Trainer with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Delete trainer
    $stmt = $conn->prepare("DELETE FROM trainers WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        respond("Trainer deleted successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

else {
    respond("Invalid request method", 405);
}

$conn->close();
?>
