<?php
mysqli_report(MYSQLI_REPORT_OFF);

require_once __DIR__ . "/env.php";

/*
 * Defaults are the local XAMPP values, so development is unchanged.
 * On the server these come from /etc/nrs/nrs.ini, which lives outside
 * the web root.
 */

$host     = nrs_env("DB_HOST", "localhost");
$port     = (int) nrs_env("DB_PORT", "3306");
$user     = nrs_env("DB_USER", "root");
$password = nrs_env("DB_PASSWORD", "");
$database = nrs_env("DB_NAME", "nrs");

$conn = new mysqli(
    $host,
    $user,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
