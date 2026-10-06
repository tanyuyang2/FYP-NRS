<?php

require_once __DIR__ . "/env.php";

/*
 * APP_ALLOWED_ORIGINS is a comma-separated allowlist. In production the
 * frontend is served from the same origin as the API, so no CORS header is
 * needed at all; the allowlist only matters for the Vite dev server and for
 * any future separate frontend host.
 *
 * Moving to https://example.com later is a change to this one setting.
 */

$allowedOrigins = array_filter(array_map(
    "trim",
    explode(",", nrs_env("APP_ALLOWED_ORIGINS", "http://localhost:5173"))
));

$origin = isset($_SERVER["HTTP_ORIGIN"]) ? $_SERVER["HTTP_ORIGIN"] : "";

if ($origin !== "" && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}
