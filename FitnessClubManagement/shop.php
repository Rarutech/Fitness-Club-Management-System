<?php
include 'database.php';

$query = "SELECT * FROM inventory";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='product'>";
    echo "<img src='uploads/" . $row['image'] . "' alt='Product Image'>";
    echo "<h3>" . $row['product_name'] . "</h3>";
    echo "<p>" . $row['description'] . "</p>";
    echo "<p>₱" . number_format($row['price'], 2) . "</p>";
    echo "<a href='cart.php?add=" . $row['id'] . "'>Add to Cart</a>";
    echo "</div>";
}
?>
