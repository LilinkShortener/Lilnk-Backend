<?php
include 'config.php';

/**
 * Retrieve user link statistics and earnings
 * Method: POST
 * Request Body:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1
 * }
 * Response:
 * Success: {
 *     "user_id": 1,
 *     "links": [
 *         {
 *             "short_url": "abcd1234",
 *             "original_url": "http://example.com",
 *             "created_at": "2024-08-01 12:00:00",
 *             "access_count": 100,
 *             "earnings": 20.00
 *         },
 *         ...
 *     ],
 *     "total_earnings": 50.00
 * }
 * Error: {"error": "User not found"}
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $data['user_id'];

    $stmt = $pdo->prepare("SELECT total_earnings FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $totalEarnings = $user['total_earnings'];

        $stmt = $pdo->prepare("SELECT short_url, original_url, created_at, access_count, with_ads FROM links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $linkData = [];
        foreach ($links as $link) {
            $earnings = 0;
            if ($link['with_ads']) {
                $earnings = $link['access_count'] * earnings_per_click;
            }
            $linkData[] = [
                'short_url' => $link['short_url'],
                'original_url' => $link['original_url'],
                'created_at' => $link['created_at'],
                'access_count' => $link['access_count'],
                'earnings' => $earnings
            ];
        }

        echo json_encode([
            'user_id' => $userId,
            'links' => $linkData,
            'total_earnings' => $totalEarnings
        ]);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
}
?>
