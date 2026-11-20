<?php 
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurantsystem";
//creacion de la conexion
$conection = mysqli_connect($servername, $username, $password, $dbname);

//checar la conexion
if(!$conection){
    die("Connection failed " . mysqli_connect_error());
}echo "Connected succesfully";
?>
