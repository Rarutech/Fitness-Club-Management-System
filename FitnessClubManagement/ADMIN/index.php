<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body>
    <!-- Sidebar Navigation -->
    <ul id="side-nav">
        <!-- Logo -->
        <li class="logo">
            <img src="" alt="Logo">
        </li>
        <!-- Navigation Links -->
        <li><a href="index.php?page=dashboard" class="<?= ($_GET['page'] ?? 'dashboard') == 'dashboard' ? 'active' : ''; ?>"><i class="ri-dashboard-3-line"></i>Dashboard</a></li>
        <li><a href="index.php?page=members" class="<?= ($_GET['page'] ?? '') == 'members' ? 'active' : ''; ?>"><i class="ri-group-fill"></i>Members</a></li>
        <li><a href="index.php?page=schedule" class="<?= ($_GET['page'] ?? '') == 'schedule' ? 'active' : ''; ?>"><i class="ri-calendar-2-line"></i>Schedule</a></li>
        <li><a href="index.php?page=attendance" class="<?= ($_GET['page'] ?? '') == 'attendance' ? 'active' : ''; ?>"><i class="ri-check-line"></i>Attendance</a></li>
        <li><a href="index.php?page=inventory" class="<?= ($_GET['page'] ?? '') == 'inventory' ? 'active' : ''; ?>"><i class="ri-store-fill"></i>Inventory</a></li>
        <li><a href="index.php?page=orders" class="<?= ($_GET['page'] ?? '') == 'orders' ? 'active' : ''; ?>"><i class="ri-shopping-bag-4-fill"></i>Orders</a></li>
        <li><a href="index.php?page=report" class="<?= ($_GET['page'] ?? '') == 'report' ? 'active' : ''; ?>"><i class="ri-bar-chart-2-fill"></i>Report</a></li>
        <li><a href="index.php?page=settings" class="<?= ($_GET['page'] ?? '') == 'settings' ? 'active' : ''; ?>"><i class="ri-settings-4-line"></i>Settings</a></li>
    </ul>

    <!-- Main Content -->
    <div class="content">
        <!-- Header Section -->
        <header>
            <!-- Left side of the header -->
            <div class="header-left">
                <?php
                    // Display page title based on the selected page
                    $page = $_GET['page'] ?? 'dashboard'; // Default page is 'dashboard'
                    $page_titles = [
                        'dashboard' => 'Dashboard',
                        'members' => 'Members',
                        'schedule' => 'Schedule',
                        'attendance' => 'Attendance',
                        'report' => 'Report',
                        'orders' => 'Orders',
                        'inventory' => 'Inventory',
                        'settings' => 'Settings'
                    ];
                    echo "<h2>" . $page_titles[$page] . "</h2>";
                ?>
            </div>

            <!-- Right side of the header (Notification, User Info, and Logout) -->
            <div class="header-right">
                <button class="notification-btn">
                    <i class="ri-notification-3-line"></i>
                </button>
                <div class="user-info">
                    <?php if (isset($_SESSION['username'])): ?>
                        <img src="img/01.png" alt="Admin" class="user-avatar">
                        <div class="user-details">
                            <h3><?php echo $_SESSION['username']; ?></h3>
                            <p><?php echo $_SESSION['role']; ?></p>
                        </div>
                        <a href="logout.php" class="logout-btn">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <?php
        // Load the selected page dynamically
        $allowed_pages = ['dashboard', 'members', 'schedule', 'attendance', 'report', 'inventory', 'orders', 'settings'];
        $page = $_GET['page'] ?? 'dashboard'; // Default to 'dashboard' page

        if (in_array($page, $allowed_pages)) {
            include "$page.php";
        } else {
            echo "<h1>Page Not Found</h1>";
        }
        ?>
    </div>

</body>
</html>