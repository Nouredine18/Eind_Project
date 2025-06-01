<?php
$host = 'localhost';
$username = 'root';
$password = ''; 
$database = 'ecoligo_data'; 

function connectToDatabase($host, $username, $password, $database) {
    $conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

$conn = connectToDatabase($host, $username, $password, $database);


?>

