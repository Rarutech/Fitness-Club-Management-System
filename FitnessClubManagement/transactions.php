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

### POST Method - Create Transaction ###
if ($method == "POST") {
    if (empty($input["member_id"]) || empty($input["product_id"]) || empty($input["quantity"]) || empty($input["total_price"])) {
        respond("Member ID, Product ID, Quantity, and Total Price are required", 400);
    }

    $member_id = $input["member_id"];
    $product_id = $input["product_id"];
    $quantity = $input["quantity"];
    $total_price = $input["total_price"];

    // Insert Query
    $stmt = $conn->prepare("INSERT INTO transactions (member_id, product_id, quantity, total_price) 
                           VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $member_id, $product_id, $quantity, $total_price);

    try {
        if ($stmt->execute()) {
            respond("Transaction created successfully");
        } else {
            respond("Error: " . $stmt->error, 500);
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error executing SQL: " . $e->getMessage());
        respond("An error occurred. Please try again later.", 500);
    }

    $stmt->close();
}

### GET Method - Read Transaction(s) ###
elseif ($method == "GET") {
    if (isset($_GET["member_id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM transactions WHERE id='$member_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $transaction = $result->fetch_assoc();
            respond($transaction);
        } else {
            respond("Transaction not found", 404);
        }
    } else {
        $sql = "SELECT * FROM transactions";
        $result = $conn->query($sql);
        $transactions = [];

        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        respond($transactions);
    }
}

### PUT Method - Update Transaction ###
elseif ($method == "PUT") {
    if (empty($input["id"]) || empty($input["member_id"]) || empty($input["product_id"]) || empty($input["quantity"]) || empty($input["total_price"])) {
        respond("Transaction ID, Member ID, Product ID, Quantity, and Total Price are required", 400);
    }

    $id = $input["id"];
    $member_id = $input["member_id"];
    $product_id = $input["product_id"];
    $quantity = $input["quantity"];
    $total_price = $input["total_price"];

    // Check if the transaction with the given ID exists in the database
    $sql = "SELECT id FROM transactions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        respond("Error: Transaction with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Proceed with the update if the transaction exists
    $stmt = $conn->prepare("UPDATE transactions SET member_id=?, product_id=?, quantity=?, total_price=? WHERE id=?");
    $stmt->bind_param("iiidi", $member_id, $product_id, $quantity, $total_price, $id);

    try {
        if ($stmt->execute()) {
            respond("Transaction updated successfully");
        } else {
            respond("Error: " . $stmt->error, 500);
        }
    } catch (mysqli_sql_exception $e) {
        error_log("Error executing SQL: " . $e->getMessage());
        respond("An error occurred. Please try again later.", 500);
    }

    $stmt->close();
}

### DELETE Method - Delete Transaction ###
elseif ($method == "DELETE") {
    if (empty($input["id"])) {
        respond("Transaction ID is required", 400);
    }

    $id = $input["id"];

    // Check if the transaction with the given ID exists
    $sql = "SELECT id FROM transactions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    // If no matching transaction is found, respond with an error
    if ($stmt->num_rows === 0) {
        respond("Error: Transaction with the provided ID does not exist", 404);
    }
    $stmt->close();

    // Proceed to delete the transaction if it exists
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        respond("Transaction deleted successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

else {
    respond("Invalid request method", 405);
}
?>
