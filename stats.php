<?php
include 'config.php';

/**
 * Retrieve user link statistics or complete user statistics
 * Method: POST
 * Request Body for link statistics:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "link_stats"
 * }
 * 
 * Request Body for withdrawals statistics:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "withdrawals_stats"
 * }
 * 
 * Response for link statistics:
 * Success: {
 *     "user_id": 1,
 *     "links": [
 *         {
 *             "short_url": "abcd1234",
 *             "original_url": "http://example.com",
 *             "created_at": "2024-08-01 12:00:00",
 *             "access_count": 100,
 *             "earnings": 200
 *         },
 *         ...
 *     ],
 *     "total_earnings": 5000
 * }
 * 
 * Response for withdrawals statistics:
 * Success: {
 *     "user_id": 1,
 *     "email": "user@example.com",
 *     "registration_time": "2024-08-01 12:00:00",
 *     "total_links": 10,
 *     "total_clicks": 1000,
 *     "ads_links": 5,
 *     "no_ads_links": 5,
 *     "ads_earnings": 2000,
 *     "current_balance": 3000,
 *     "withdrawals": [
 *         {
 *             "id": 1,
 *             "user_id": 1,
 *             "iban": "IR123456789012345678901234567890",
 *             "amount": 100.00,
 *             "name": "John",
 *             "surname": "Doe",
 *             "request_time": "2024-08-02 12:00:00"
 *             "status": 1
 *         },
 *         ...
 *     ]
 * }
 * 
 * Error:
 * - {"error": "Invalid API Key", "code": 3000} - API Key اشتباه است.
 * - {"error": "User not found", "code": 3001} - کاربر پیدا نشد.
 * - {"error": "Invalid action", "code": 3002} - اکشن نادرست است.
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 3000]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $data['user_id'];
    $action = isset($data['action']) ? $data['action'] : '';

    // Retrieve user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($action === 'link_stats') {
            // Original link statistics functionality
            $stmt = $pdo->prepare("SELECT short_url, original_url, created_at, access_count, with_ads FROM links WHERE user_id = ?");
            $stmt->execute([$userId]);
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $linkData = [];
            $totalEarnings = 0;

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
                $totalEarnings += $earnings;
            }

            echo json_encode([
                'user_id' => $userId,
                'links' => $linkData,
                'total_earnings' => $totalEarnings
            ]);

        } elseif ($action === 'withdrawals_stats') {
            // New functionality: Withdrawals user statistics
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_links, SUM(access_count) as total_clicks, 
                                   SUM(CASE WHEN with_ads = 1 THEN 1 ELSE 0 END) as ads_links,
                                   SUM(CASE WHEN with_ads = 0 THEN 1 ELSE 0 END) as no_ads_links,
                                   SUM(CASE WHEN with_ads = 1 THEN access_count * ? ELSE 0 END) as ads_earnings
                                   FROM links WHERE user_id = ?");
            $stmt->execute([earnings_per_click, $userId]);
            $linkStats = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ?");
            $stmt->execute([$userId]);
            $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'registration_time' => $user['registration_time'],
                'total_links' => $linkStats['total_links'],
                'total_clicks' => $linkStats['total_clicks'],
                'ads_links' => $linkStats['ads_links'],
                'no_ads_links' => $linkStats['no_ads_links'],
                'ads_earnings' => $linkStats['ads_earnings'],
                'current_balance' => $user['total_earnings'],
                'withdrawals' => $withdrawals
            ]);

        } else {
            echo json_encode(['error' => 'Invalid action', 'code' => 3002]);
        }

    } else {
        echo json_encode(['error' => 'User not found', 'code' => 3001]);
    }
}
?>
