<?php
include "database.php";

function respond($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($method !== 'POST') {
    respond(["message" => "Invalid request method"], 405);
}

if ($contentType === "application/json") {
    $input = json_decode(file_get_contents('php://input'), true);
} elseif ($contentType === "application/x-www-form-urlencoded" || $contentType === "multipart/form-data") {
    $input = $_POST;
} else {
    parse_str(file_get_contents('php://input'), $input);
}

if (empty($input["email"]) || empty($input["password"])) {
    respond(["message" => "Email and password are required"], 400);
}

$email = $input["email"];
$password = $input["password"];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    respond(["message" => "Invalid email or password"], 401);
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    respond(["message" => "Invalid email or password"], 401);
}

$token = bin2hex(random_bytes(16));
$sql = "UPDATE users SET token='$token' WHERE email='$email'";

if ($conn->query($sql) === TRUE) {
    respond(["message" => "Login successful", "token" => $token, "email" => $email]);
} else {
    respond(["message" => "Error: " . $conn->error], 500);
}
?>