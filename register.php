<?php
include 'config.php';

/**
 * User Registration API
 * Method: POST
 * Request Body:
 * {
 *     "email": "user@example.com",
 *     "password": "securepassword"
 * }
 * Response:
 * Success: {"success": true, "id": 12345}
 * Error: {"error": "User already exists"}
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'User already exists']);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        if ($stmt->execute([$email, $password])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Registration failed']);
        }
    }
}
?>
