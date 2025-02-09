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
    if (empty($input["fullname"]) || empty($input["email"]) || empty($input["password"])) {
        respond("All fields are required", 400);
    }

    $fullname = $input["fullname"];
    $email = $input["email"];
    $password = password_hash($input['password'], PASSWORD_BCRYPT);
    $role = $input['role'] ?? 'member';

    $sql = "INSERT INTO users (fullname, email, password, role) VALUES ('$fullname', '$email', '$password', '$role')";

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
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        parse_str(file_get_contents('php://input'), $input);

        if (empty($input["fullname"]) || 
            empty($input["email"])) {
            respond("Fullname and email are required", 400);
        }

        $fullname = $input["fullname"];
        $email = $input["email"];
        $password = !empty($input['password']) ? password_hash($input['password'], PASSWORD_BCRYPT) : null;
        $role = $input['role'] ?? 'member';

        $sql = "UPDATE users SET fullname='$fullname', email='$email', role='$role'";
        if ($password) {
            $sql .= ", password='$password'";
        }
        $sql .= " WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            respond("User updated successfully");
        } else {
            respond("Error: " . $conn->error, 500);
        }
    } else {
        respond("User ID is required", 400);
    }
} elseif ($method == "DELETE") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "DELETE FROM users WHERE id='$id'";

        if ($conn->query($sql) === TRUE) {
            respond("User deleted successfully");
        } else {
            respond("Error: " . $conn->error, 500);
        }
    } else {
        respond("User ID is required", 400);
    }
}
?>