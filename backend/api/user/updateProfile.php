<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";

$data = json_decode(file_get_contents("php://input"), true);
$username = $data["username"] ?? "";
$bio = $data["bio"] ?? "";
$profilePicture = $data["profile_picture"] ?? null;
$frameId = $data["frame_id"] ?? null;
$userId = $_SESSION["user_id"];

if (!is_string($username) || !is_string($bio)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid profile details"
	]);
	exit;
}

if ($profilePicture !== null && !is_string($profilePicture)) {
	http_response_code(400);
	echo json_encode([
		"success" => false,
		"message" => "Invalid profile picture"
	]);
	exit;
}

if ($username === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username is required"
    ]);
    exit;
}

if (!preg_match("/^(?!\s).{2,30}(?<!\s)$/u", $username)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Username must be 2 to 30 characters and cannot start or end with spaces"
    ]);
    exit;
}

if (mb_strlen($bio) > 160) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Bio must be 160 characters or less"
    ]);
    exit;
}

if ($frameId !== null && !is_numeric($frameId)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid frame ID"
    ]);
    exit;
}

if (!$conn->begin_transaction()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE users
     SET username = ?, bio = ?, profile_picture = ?
     WHERE user_id = ?"
);

if (!$stmt) {
	$conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param(
    "sssi",
    $username,
    $bio,
    $profilePicture,
    $userId
);

if (!$stmt->execute()) {
	$conn->rollback();
	if ($stmt->errno === 1062) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Username is already taken"
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();

if ($frameId === null) {
    $stmt = $conn->prepare(
        "DELETE FROM user_equipped_cosmetic
         WHERE user_id = ? AND cosmetic_type_id = 1"
    );
} else {
    $stmt = $conn->prepare(
        "INSERT INTO user_equipped_cosmetic (
			user_id, 
			cosmetic_type_id, 
			cosmetic_id
		)
         VALUES (?, 1, ?)
         ON DUPLICATE KEY UPDATE cosmetic_id = VALUES(cosmetic_id)"
    );
}

if (!$stmt) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

if ($frameId === null) {
    $stmt->bind_param("i", $userId);
} else {
    $stmt->bind_param("ii", $userId, $frameId);
}

if (!$stmt->execute()) {
    $conn->rollback();
    if ($stmt->errno === 1452) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid or unowned profile frame"
        ]);
        exit;
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->close();
$conn->commit();

echo json_encode([
    "success" => true,
    "message" => "Profile updated successfully"
]);