<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$cosmeticId = $data["cosmetic_id"] ?? null;
$referenceKey = "shop_purchase:" . $cosmeticId;
$userId = $_SESSION["user_id"];

if ($cosmeticId === null || !is_numeric($cosmeticId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid cosmetic ID"
    ]);
    exit;
}

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT price
     FROM cosmetic
     WHERE cosmetic_id = ?"
);

if (!$stmt) {
	$conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("i", $cosmeticId);

if (!$stmt->execute()) {
	$conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$result = $stmt->get_result();
$stmt->close();
$cosmetic = $result->fetch_assoc();

if (!$cosmetic) {
    $conn->rollback();
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Cosmetic not found"
    ]);
    exit;
}

$price = $cosmetic["price"];

$stmt = $conn->prepare(
    "INSERT INTO user_cosmetic (user_id, cosmetic_id)
     VALUES (?, ?)"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("ii", $userId, $cosmeticId);

if (!$stmt->execute()) {
    $conn->rollback();
    if ($stmt->errno === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "You already own this cosmetic"
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

$stmt = $conn->prepare(
    "UPDATE users
     SET coin = coin - ?
     WHERE user_id = ? AND coin >= ?"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("iii", $price, $userId, $price);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$updated = $stmt->affected_rows;
$stmt->close();

if ($price > 0 && $updated === 0) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Not enough coins"
    ]);
    exit;
}

$amount = -$price;

$stmt = $conn->prepare(
    "INSERT INTO transaction (
		user_id, 
		amount, 
		reason, 
		cosmetic_id, 
		reference_key
	)
     VALUES (?, ?, 'shop_purchase', ?, ?)"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("iiis", $userId, $amount, $cosmeticId, $referenceKey);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server Error"]);
    exit;
}

$stmt->close();

if (!$conn->commit()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server Error"]);
    exit;
}

echo json_encode([
    "success" => true,
	"amount" => $amount,
    "message" => "Cosmetic purchased successfully"
]);