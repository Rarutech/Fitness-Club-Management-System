<?php
include "database.php";  // Include the database connection

$method = $_SERVER['REQUEST_METHOD'];  // Get the request method (GET, POST, PUT, DELETE)
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

// Read the input based on the content type (JSON or URL-encoded)
if ($contentType === "application/json") {
    $input = json_decode(file_get_contents('php://input'), true);
} elseif ($contentType === "application/x-www-form-urlencoded" || $contentType === "multipart/form-data") {
    $input = $_POST;
} else {
    parse_str(file_get_contents('php://input'), $input);
}

// Respond function for consistent JSON responses
function respond($message, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode(["status" => $status == 200 ? "success" : "error", "message" => $message]);
    exit();
}

### POST Method - Create Member ###
if ($method == "POST") {
    if (empty($input["user_id"]) || empty($input["membership_start"]) || empty($input["membership_end"]) || empty($input["role"]) || empty($input["status"])) {
        respond("User ID, membership start date, membership end date, role, and status are required", 400);
    }

    $user_id = $input["user_id"];
    $membership_start = $input["membership_start"];
    $membership_end = $input["membership_end"];
    $role = $input["role"];
    $status = $input["status"];

    // Check if the user_id already exists in the 'members' table
    $sql = "SELECT id FROM members WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    // If a record exists, return an error response
    if ($stmt->num_rows > 0) {
        respond("Error: This user already has an active membership", 400);
    }
    $stmt->close();

    // Proceed with creating the new member
    $stmt = $conn->prepare("INSERT INTO members (user_id, membership_start, membership_end, role, status)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $membership_start, $membership_end, $role, $status);

    try {
        if ($stmt->execute()) {
            respond("Member created successfully");
        } else {
            respond("Error: " . $stmt->error, 500);
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error executing SQL: " . $e->getMessage());
        respond("An error occurred. Please try again later.", 500);
    }

    $stmt->close();
}

### GET Method - Read Member(s) ###
elseif ($method == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM members WHERE id='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $member = $result->fetch_assoc();
            respond($member);
        } else {
            respond("Member not found", 404);
        }
    } else {
        $sql = "SELECT * FROM members";
        $result = $conn->query($sql);
        $members = [];

        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
        respond($members);
    }
}

### PUT Method - Update Member ###
elseif ($method == "PUT") {
    if (empty($input["id"]) || empty($input["user_id"]) || empty($input["membership_start"]) || empty($input["membership_end"]) || empty($input["role"]) || empty($input["status"])) {
        respond("Member ID, user ID, membership start date, and membership end date, role, and status are required", 400);
    }

    $id = $input["id"];
    $user_id = $input["user_id"];
    $membership_start = $input["membership_start"];
    $membership_end = $input["membership_end"];
    $role = $input["role"];
    $status = $input["status"];

    // Check if the member with the given ID exists in the database
    $sql = "SELECT id FROM members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        respond("Error: Member with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Check if the user_id matches the existing member (optional check)
    $sql = "SELECT user_id FROM members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($existing_user_id);
    $stmt->fetch();

    if ($existing_user_id != $user_id) {
        respond("Error: User ID does not match the existing member", 400);
    }
    $stmt->close();

    // Proceed with the update if the member exists and user_id matches
    $stmt = $conn->prepare("UPDATE members SET user_id=?, membership_start=?, membership_end=?, role=?, status=?, WHERE id=?");
    $stmt->bind_param("issi", $user_id, $membership_start, $membership_end, $role, $status, $id);

    try {
        if ($stmt->execute()) {
            respond("Member updated successfully");
        } else {
            respond("Error: " . $stmt->error, 500);
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error executing SQL: " . $e->getMessage());
        respond("An error occurred. Please try again later.", 500);
    }

    $stmt->close();
}


### DELETE Method - Delete Member ###
elseif ($method == "DELETE") {
    if (empty($input["id"])) {
        respond("Member ID is required", 400);
    }

    $id = $input["id"];

    // Check if the member with the given ID exists
    $sql = "SELECT id FROM members WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    // If no matching member is found, respond with an error
    if ($stmt->num_rows === 0) {
        respond("Error: Member with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Proceed to delete the member if it exists
    $stmt = $conn->prepare("DELETE FROM members WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        respond("Member deleted successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

else {
    respond("Invalid request method", 405);
}
?>
