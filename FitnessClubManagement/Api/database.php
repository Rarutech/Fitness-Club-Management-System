<?php 

    $hostname = "localhost";
    $dbUsers = "root";
    $dbPassword = "";
    $dbName = "fitnessclubmanagement_db";

    $conn = mysqli_connect($hostname, $dbUsers, $dbPassword, $dbName);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    

?>