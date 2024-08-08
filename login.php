<?php
include 'config.php';

/**
 * User Login API
 * Method: POST
 * Request Body:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "email": "example@example.com",
 *     "password": "password123"
 * }
 * Response:
 * Success: {
 *     "success": true,
 *     "id": 1,
 *     "links": [
 *         {
 *             "short_url": "abcd1234",
 *             "original_url": "http://example.com",
 *             "created_at": "2024-08-01 12:00:00",
 *             "access_count": 100,
 *             "earnings": 20.00
 *         },
 *         ...
 *     ]
 * }
 * Error: {"error": "Invalid credentials"}
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'];
    $password = $data['password'];

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $userId = $user['id'];

        $stmt = $pdo->prepare("SELECT * FROM links WHERE user_id = ?");
        $stmt->execute([$userId]);
        $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'id' => $userId, 'links' => $links]);
    } else {
        echo json_encode(['error' => 'Invalid credentials']);
    }
}
?>
