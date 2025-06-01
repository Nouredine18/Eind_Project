<?php
session_start();
include '../connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welkom bij Ecoligo Collective</h1>
    
    <nav>
        <ul>
            <li><a href="view_travel_history.php">Mijn Reisgeschiedenis</a></li>
            <li><a href="summary_co2.php">CO2-uitstoot Samenvatting</a></li>
            <li><a href="add_travel.php">Reisbestemming Toevoegen</a></li>
            <li><a href="../profile.php">Profiel</a></li>
            <li><a href="../home.php">Home</a></li>
        </ul>
    </nav>
</body>
</html>
