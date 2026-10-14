<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);
$name = $data["name"] ?? "";

if (!is_string($name)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid tag name"
	]);
	exit;
}

$name = trim($name);

if ($name === "") {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Tag name is required"
	]);
	exit;
}

if (mb_strlen($name) > 30) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Tag name cannot exceed 30 characters"
	]);
	exit;
}

$stmt = $conn->prepare(
	"INSERT INTO tag (name)
	 VALUES (?)"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("s", $name);

if (!$stmt->execute()) {
	if ($stmt->errno === 1062) {
		http_response_code(409);
		echo json_encode([
			"success" => false,
			"message" => "Tag already exists"
		]);
		exit;
	}

	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->close();

echo json_encode([
	"success" => true,
	"message" => "Tag added"
]);