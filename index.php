<?php
require_once 'classUser.php';
require_once 'classUsers.php';

$address = "localhost";
$database = "slmax";
$username = "root";
$password = "";
$charset = 'utf8';
$pdo = new PDO("mysql:host=$address;dbname=$database;charset=$charset", $username, $password);
