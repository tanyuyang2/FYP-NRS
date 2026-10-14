<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);
$categoryId = $data["category_id"] ?? null;
$name = $data["name"] ?? "";

if (!$categoryId || !is_numeric($categoryId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid category ID"
	]);
	exit;
}

if (!is_string($name)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid category name"
	]);
	exit;
}

$name = trim($name);

if ($name === "") {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Category name is required"
	]);
	exit;
}

if (mb_strlen($name) > 30) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Category name cannot exceed 30 characters"
	]);
	exit;
}

$stmt = $conn->prepare(
	"SELECT category_id
	 FROM category
	 WHERE category_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $categoryId);

if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "Category not found"
	]);
	exit;
}

$stmt = $conn->prepare(
	"UPDATE category
	 SET category_name = ?
	 WHERE category_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("si", $name, $categoryId);

if (!$stmt->execute()) {
	if ($stmt->errno === 1062) {
		http_response_code(409);
		echo json_encode([
			"success" => false,
			"message" => "Category already exists"
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
	"message" => "Category updated"
]);