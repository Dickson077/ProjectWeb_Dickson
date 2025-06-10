<?php

$host = "localhost";
$username ="root";
$password = "";
$database = "penmaru";

$koneksi = new mysqli($host, $username, $password, $database);
if($koneksi) {
    echo "Database Terkoneksi";
}else{
    echo "Database Tidak Terkoneksi";
}
?>