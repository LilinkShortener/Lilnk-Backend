<?php
include 'config.php';

/**
 * Shorten Link API
 * Method: POST
 * Request Body:
 * {
 *     "user_id": 12345,
 *     "original_link": "http://example.com",
 *     "short_link": "customname",  // یا "" برای نام رندوم
 *     "with_ads": true  // یا false برای بدون تبلیغ
 * }
 * Response:
 * Success: {"success": true, "short_link": "abcd"}
 * Error: {"error": "Short link already exists"}
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $userId = $data['user_id'];
    $originalLink = $data['original_link'];
    $shortLink = $data['short_link'] ? $data['short_link'] : bin2hex(random_bytes(4));
    $withAds = isset($data['with_ads']) ? $data['with_ads'] : true;

    // Check if short link already exists
    $stmt = $pdo->prepare("SELECT id FROM links WHERE short_url = ?");
    $stmt->execute([$shortLink]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'Short link already exists']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO links (short_url, original_url, user_id, created_at, with_ads) VALUES (?, ?, ?, NOW(), ?)");
        if ($stmt->execute([$shortLink, $originalLink, $userId, $withAds])) {
            echo json_encode(['success' => true, 'short_link' => $shortLink]);
        } else {
            echo json_encode(['error' => 'Failed to shorten link']);
        }
    }
}
?>
