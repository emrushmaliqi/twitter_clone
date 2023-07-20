<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include '../db.php';
session_start();

if (!isset($_SESSION['logged'])) {
    header("HTTP/1.1 401 Unauthorized");
    die();
}

$objDb = new Database;
$conn = $objDb->connect();
