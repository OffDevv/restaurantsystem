<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurantsystem";

$conection = mysqli_connect(
    $servername,
    $username,
    $password,
    $dbname
);

mysqli_set_charset($conection, "utf8mb4");