<?php
include "database.php";  // Make sure you include your database connection here

$method = $_SERVER['REQUEST_METHOD'];
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

### POST Method - Create Product ###
if ($method == "POST") {

    if (empty($input["product_name"]) || empty($input["price"]) || !isset($input["stock_quantity"])) {
        respond("Product name, price, and stock quantity are required", 400);
    }

    $product_name = $input["product_name"];
    $description = $input["description"] ?? null;
    $price = $input["price"];
    $stock_quantity = $input["stock_quantity"];


    $stmt = $conn->prepare("INSERT INTO inventory (product_name, description, price, stock_quantity) 
                           VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $product_name, $description, $price, $stock_quantity);

    if ($stmt->execute()) {
        respond("Product created successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

### GET Method - Read Product(s) ###
elseif ($method == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        $sql = "SELECT * FROM inventory WHERE id='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            respond($product);
        } else {
            respond("Product not found", 404);
        }
    } else {
        $sql = "SELECT * FROM inventory";
        $result = $conn->query($sql);
        $products = [];

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        respond($products);
    }
}

### PUT Method - Update Product ###
elseif ($method == "PUT") {
    if (empty($input["id"]) || empty($input["product_name"]) || empty($input["price"]) || !isset($input["stock_quantity"])) {
        respond("Product ID, product name, price, and stock quantity are required", 400);
    }

    $id = $input["id"];
    $product_name = $input["product_name"];
    $description = $input["description"] ?? null;  // Optional
    $price = $input["price"];
    $stock_quantity = $input["stock_quantity"];

        if (empty($product_name) || empty($price) || empty($stock_quantity)) {
            echo "Please fill in all required fields.";
            exit;
        }

    $stmt = $conn->prepare("UPDATE inventory SET product_name=?, description=?, price=?, stock_quantity=? WHERE id=?");
    $stmt->bind_param("ssdis", $product_name, $description, $price, $stock_quantity, $id);

    if ($stmt->execute()) {
        respond("Product updated successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

### DELETE Method - Delete Product ###
elseif ($method == "DELETE") {
    if (empty($input["id"])) {
        respond("Product ID is required", 400);
    }

    $id = $input["id"];

    $stmt = $conn->prepare("DELETE FROM inventory WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        respond("Product deleted successfully");
    } else {
        respond("Error: " . $stmt->error, 500);
    }
    $stmt->close();
}

else {
    respond("Invalid request method", 405);  // If the method is not POST, GET, PUT, or DELETE
}
?>
