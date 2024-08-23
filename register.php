<?php
include 'config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * User Registration API
 * Method: POST
 * Request Body:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "email": "example@example.com",
 *     "password": "password123"
 * }
 * Response:
 * Success: {"success": true, "id": 1}
 * Error:
 * - {"error": "Invalid API Key", "code": 1000} - API Key اشتباه است.
 * - {"error": "User already exists", "code": 1001} - کاربر با این ایمیل قبلا ثبت‌نام کرده است.
 * - {"error": "Registration failed", "code": 1002} - مشکلی در ثبت‌نام کاربر رخ داده است.
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 1000]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['error' => 'User already exists', 'code' => 1001]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        if ($stmt->execute([$email, $password])) {
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } else {
            echo json_encode(['error' => 'Registration failed', 'code' => 1002]);
        }
    }
}
?>
