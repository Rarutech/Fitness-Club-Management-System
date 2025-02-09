<?php
    include '../includes/database.php';

    // Check if a search term is provided
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    // Sanitize the search term (e.g., integers for `id` and `user_id`, strings for `membership_start` and `membership_end`)
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);

    // Modify the query to filter by `id` or `user_id` if search term is provided
    $query = "SELECT * FROM members";
    if ($searchTerm) {
        // Add filtering for both `id` and `user_id`
        $query .= " WHERE id LIKE '%$searchTerm%' OR user_id LIKE '%$searchTerm%'";
    }

    $result = mysqli_query($conn, $query);

    // Check if query was successful
    if (!$result) {
        // Enhanced error message for production environments
        echo "Error: " . mysqli_error($conn);
        exit; // Stop further execution if query fails
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="memberStyle.css">
</head>
<body>
<div class="title">
        <h1>User Management</h1>
        <p>Manage your gym members and their membership status</p>
    </div>

    <!-- Main Content -->
    <div class="members-content">
        <header>
            <h2>All Users</h2>

            <!-- Search Bar -->
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="members">
                <input type="text" name="search" placeholder="Search by ID or User ID" class="search-input" 
                value="<?php echo isset($_GET['search']) ? ($_GET['search']) : ''; ?>">

                <select name="status" class="filter-input">
                    <option value="">Filter by Status</option>
                    <option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>

                <button type="submit" class="search-button">Search</button>
            </form>

        </header>

        <!-- Members Table -->
        <table>
            <thead class="table-header">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Membership Start</th>
                    <th>Membership End</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch and display members from the database
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . ($row['id']) . "</td>";
                    echo "<td>" . ($row['user_id']) . "</td>";
                    echo "<td>" . ($row['membership_start']) . "</td>";
                    echo "<td>" . ($row['membership_end']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>