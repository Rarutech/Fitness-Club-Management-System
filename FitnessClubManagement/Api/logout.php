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

if (empty($input["email"])) {
    respond(["message" => "Email is required"], 400);
}

$email = $input["email"];

$sql = "UPDATE users SET token=NULL WHERE email='$email'";

if ($conn->query($sql) === TRUE) {
    respond(["message" => "Logout successful"]);
} else {
    respond(["message" => "Error: " . $conn->error], 500);
}
?>