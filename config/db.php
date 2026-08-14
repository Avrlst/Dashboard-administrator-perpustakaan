<?php

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'db_perpustakaan';

$connect = mysqli_connect($host,$user,$password,$dbname);

if (!$connect){
    die('koneksi database gagal'.mysqli_connect_error());
}
?>