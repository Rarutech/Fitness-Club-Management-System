<?php
include '../database.php';

// Handle Add Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    $query = "INSERT INTO inventory (product_name, description, price, stock_quantity) 
              VALUES ('$product_name', '$description', '$price', '$stock_quantity')";
    mysqli_query($conn, $query);
    header("Location: index.php?page=inventory");
    exit();
}

// Handle Edit Product
$edit_id = null;
$edit_product = null;

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM inventory WHERE id=$edit_id");
    $edit_product = mysqli_fetch_assoc($edit_result);
}

// Handle Update Product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $id = intval($_POST['id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);

    $query = "UPDATE inventory SET 
                product_name='$product_name', 
                description='$description', 
                price='$price', 
                stock_quantity='$stock_quantity' 
              WHERE id=$id";
    mysqli_query($conn, $query);
    header("Location: index.php?page=inventory");
    exit();
}

// Handle Delete Product
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM inventory WHERE id=$id");
    header("Location: index.php?page=inventory");
    exit();
}

// Fetch all inventory products
$result = mysqli_query($conn, "SELECT * FROM inventory");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="InventoryStyle.css">
</head>
<body>
    <div class="title">
        <h1>Inventory Management</h1>
        <p>Manage your products and stock levels</p>
    </div>

    <!-- Add/Edit Product Form -->
    <div class="inventory-form">
        <h2><?php echo $edit_product ? "Edit Product" : "Add New Product"; ?></h2>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo $edit_product['id'] ?? ''; ?>">
            <input type="text" name="product_name" placeholder="Product Name" required 
                   value="<?php echo $edit_product['product_name'] ?? ''; ?>">
            <textarea name="description" placeholder="Description"><?php echo $edit_product['description'] ?? ''; ?></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price" required 
                   value="<?php echo $edit_product['price'] ?? ''; ?>">
            <input type="number" name="stock_quantity" placeholder="Stock Quantity" required 
                   value="<?php echo $edit_product['stock_quantity'] ?? ''; ?>">
            <button type="submit" name="<?php echo $edit_product ? "update_product" : "add_product"; ?>">
                <?php echo $edit_product ? "Update Product" : "Add Product"; ?>
            </button>
        </form>
    </div>

    <!-- Inventory List -->
    <div class="inventory-content">
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo $row['stock_quantity']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="index.php?page=inventory&edit=<?php echo $row['id']; ?>" class="edit-btn">Edit</a>
                        <a href="index.php?page=inventory&delete=<?php echo $row['id']; ?>" class="delete-btn"
                           onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

<?php mysqli_close($conn); ?>
