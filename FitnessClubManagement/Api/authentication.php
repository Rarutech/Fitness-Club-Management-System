<?php
include "database.php";

function respond($message, $status = 200) {
    http_response_code($status);
    echo json_encode($message);
    exit();
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

$email = $input["email"];
$token = $input["token"];

$sql = "SELECT * FROM users WHERE email='$email' AND token='$token'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    respond(["message" => "Invalid email or token"], 401);
}

// Proceed with the authenticated action
respond(["message" => "Authentication successful"]);
?>