<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireAdmin.php";

$data = json_decode(file_get_contents("php://input"), true);
$categoryId = $data["category_id"] ?? null;

if (!$categoryId || !is_numeric($categoryId)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid category ID"
	]);
	exit;
}

$stmt = $conn->prepare(
	"DELETE FROM category
	 WHERE category_id = ?"
);

if (!$stmt) {
	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$stmt->bind_param("i", $categoryId);

if (!$stmt->execute()) {
	if ($stmt->errno === 1451) {
		http_response_code(409);
		echo json_encode([
			"success" => false,
			"message" => "Cannot delete a category that is in use"
		]);
		exit;
	}

	http_response_code(500);
	echo json_encode(["success" => false, "message" => "Server error"]);
	exit;
}

$affectedRows = $stmt->affected_rows;
$stmt->close();

if ($affectedRows === 0) {
	http_response_code(404);
	echo json_encode([
		"success" => false,
		"message" => "Category not found"
	]);
	exit;
}

echo json_encode([
	"success" => true,
	"message" => "Category deleted"
]);