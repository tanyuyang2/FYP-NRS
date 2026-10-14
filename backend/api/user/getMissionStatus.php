<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/missionProgress.php";
require __DIR__ . "/../utils/requireUser.php";

$userId = $_SESSION["user_id"];
$day = getMissionDay();
$definitions = getMissionDefinitions();
$missions = [];

foreach ($definitions as $code => $definition) {
    $progress = getMissionProgress($conn, $userId, $code, $day);

    if ($progress === null) {
        http_response_code(500);
        echo json_encode(["success" => false,"message" => "Server error"]);
        exit;
    }

    $referenceKey = getMissionReference($code, $day["date"]);
    $claimed = isMissionClaimed($conn, $userId, $referenceKey);

    if ($claimed === null) {
        http_response_code(500);
        echo json_encode(["success" => false,"message" => "Server error"]);
        exit;
    }

    $missions[] = [
        "code" => $code,
        "title" => $definition["title"],
        "progress" => min($progress, $definition["target"]),
        "target" => $definition["target"],
        "reward" => $definition["reward"],
        "claimed" => $claimed,
        "can_claim" => !$claimed && $progress >= $definition["target"]
    ];
}

echo json_encode([
    "success" => true,
    "date" => $day["date"],
	"reset_at" => $day["end"],
    "missions" => $missions
]);