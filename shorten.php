<?php
include 'config.php';

/**
 * Shorten Link API
 * Method: POST
 * Request Body:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 12345,
 *     "original_link": "http://example.com",
 *     "short_link": "customname",  // یا "" برای نام رندوم
 *     "with_ads": true  // یا false برای بدون تبلیغ
 * }
 * Response:
 * Success: {"success": true, "short_link": "abcd"}
 * Error:
 * - {"error": "Invalid API Key", "code": 4000} - API Key اشتباه است.
 * - {"error": "Short link already exists", "code": 4001} - لینک کوتاه شده تکراری است.
 * - {"error": "Failed to shorten link", "code": 4002} - مشکلی در ایجاد لینک کوتاه شده وجود دارد.
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 4000]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $data['user_id'];
    $originalLink = $data['original_link'];
    $shortLink = $data['short_link'] ? $data['short_link'] : bin2hex(random_bytes(4));
    $withAds = isset($data['with_ads']) ? $data['with_ads'] : true;

    $stmt = $pdo->prepare("SELECT id FROM links WHERE short_url = ?");
    $stmt->execute([$shortLink]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'Short link already exists', 'code' => 4001]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO links (short_url, original_url, user_id, with_ads) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$shortLink, $originalLink, $userId, $withAds])) {
            echo json_encode(['success' => true, 'short_link' => $shortLink]);
        } else {
            echo json_encode(['error' => 'Failed to shorten link', 'code' => 4002]);
        }
    }
}
?>