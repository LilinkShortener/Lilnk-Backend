<?php
include 'config.php';

/**
 * User Login API
 * Method: POST
 * Request Body:
 * {
 *     "email": "user@example.com",
 *     "password": "securepassword"
 * }
 * Response:
 * Success: {"success": true, "id": 12345, "links": [...]}
 * Error: {"error": "Invalid credentials"}
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

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
