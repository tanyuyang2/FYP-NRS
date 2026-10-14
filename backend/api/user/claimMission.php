<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";
require __DIR__ . "/../utils/missionProgress.php";

$data = json_decode(file_get_contents("php://input"), true);
$code = is_array($data) ? ($data["mission_code"] ?? null) : null;
$definitions = getMissionDefinitions();

if (!is_string($code) || !isset($definitions[$code])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid mission"
    ]);
    exit;
}

$userId = $_SESSION["user_id"];
$day = getMissionDay();
$definition = $definitions[$code];
$referenceKey = getMissionReference($code, $day["date"]);
$claimed = isMissionClaimed($conn, $userId, $referenceKey);

if ($claimed === null) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($claimed) {
    echo json_encode([
        "success" => true,
        "already_claimed" => true,
        "amount" => 0,
        "message" => "Mission reward already claimed"
    ]);
    exit;
}

$progress = getMissionProgress($conn, $userId, $code, $day);

if ($progress === null) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($progress < $definition["target"]) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "message" => "Mission is not completed"
    ]);
    exit;
}

$amount = $definition["reward"];

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO transaction (
        user_id,
        amount,
        reason,
        reference_key
    )
    VALUES (?, ?, 'daily_mission', ?)"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("iis", $userId, $amount, $referenceKey);

if (!$stmt->execute()) {
    $conn->rollback();
    if ($stmt->errno === 1062) {
        echo json_encode([
            "success" => true,
            "already_claimed" => true,
            "amount" => 0,
            "message" => "Mission reward already claimed"
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
     SET coin = coin + ?
     WHERE user_id = ?"
);

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false,"message" => "Server error"]);
    exit;
}

$stmt->bind_param("ii", $amount, $userId);

if (!$stmt->execute()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

if (!$conn->commit()) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false,"message" => "Server error"]);
    exit;
}

echo json_encode([
    "success" => true,
    "already_claimed" => false,
    "amount" => $amount,
    "message" => "You received $amount coins"
]);