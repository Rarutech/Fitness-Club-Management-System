<?php 
include "database.php";

$method = $_SERVER['REQUEST_METHOD'];
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($contentType === "application/json") {
    $input = json_decode(file_get_contents('php://input'), true);
} elseif ($contentType === "application/x-www-form-urlencoded" || $contentType === "multipart/form-data") {
    $input = $_POST;
} else {
    parse_str(file_get_contents('php://input'), $input);
}

function respond($message, $status = 200) {
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit();
}

if ($method == "POST") {
    if (empty($input["firstname"]) || 
        empty($input["lastname"]) || 
        empty($input["email"]) || 
        empty($input["password"])) {
        respond("All fields are required", 400);
    }

    $firstname = $input["firstname"];
    $lastname = $input["lastname"];
    $email = $input["email"];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);
    $role = $input['role'] ?? 'member';

    $sql = "INSERT INTO users (firstname, lastname, email, password, role) VALUES ('$firstname', '$lastname', '$email', '$password', '$role')";

    if ($conn->query($sql) === TRUE) {
        respond("User created successfully");
    } else {
        respond("Error: " . $conn->error, 500);
    }
} elseif ($method == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM users WHERE id='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            respond($user);
        } else {
            respond("User not found", 404);
        }
    } else {
        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);
        $users = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        respond($users);  
    }
} elseif ($method == "PUT") {
    if (empty($input["id"]) || empty($input["firstname"]) || empty($input["lastname"]) || empty($input["email"])) {
        respond("All fields are required", 400);
    }

    $id = $input["id"];
    $firstname = $input["firstname"];
    $lastname = $input["lastname"];
    $email = $input["email"];
    $role = $input['role'] ?? 'member';

    $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', email='$email', role='$role' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        respond("User updated successfully");
    } else {
        respond("Error: " . $conn->error, 500);
    }
} elseif ($method == "DELETE") {
    if (empty($input["id"])) {
        respond("ID is required", 400);
    }

    $id = $input["id"];

    $sql = "DELETE FROM users WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        respond("User deleted successfully");
    } else {
        respond("Error: " . $conn->error, 500);
    }
} else {
    respond("Invalid request method", 405);
}
?>