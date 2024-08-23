<?php
include 'config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * Withdraw API
 * Method: POST
 * Request Body for processing withdrawal:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "iban": "IR123456789012345678901234567890",
 *     "name": "John",
 *     "surname": "Doe"
 * }
 * Response for processing:
 * Success: {"success": true, "message": "Withdrawal processed successfully"}
 * Error Codes:
 * - {"error": "Invalid API Key", "code": 6000} - API Key اشتباه است.
 * - {"error": "Insufficient funds", "code": 6001} - موجودی کاربر کافی نیست.
 * - {"error": "Failed to process withdrawal", "code": 6004} - مشکلی در پردازش درخواست واریز وجود دارد.
 * - {"error": "User not found", "code": 6005} - کاربر پیدا نشد.
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 6000]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    processWithdrawal($data);
}

function processWithdrawal($data) {
    global $pdo;

    $userId = $data['user_id'];
    $iban = $data['iban'];
    $name = $data['name'];
    $surname = $data['surname'];

    $stmt = $pdo->prepare("SELECT total_earnings FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $totalEarnings = $user['total_earnings'];

        if ($totalEarnings > 0) {
            $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, iban, amount, name, surname) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$userId, $iban, $totalEarnings, $name, $surname])) {
                $stmt = $pdo->prepare("UPDATE users SET total_earnings = 0 WHERE id = ?");
                $stmt->execute([$userId]);

                echo json_encode(['success' => true, 'message' => 'Withdrawal processed successfully']);
            } else {
                echo json_encode(['error' => 'Failed to process withdrawal', 'code' => 6004]);
            }
        } else {
            echo json_encode(['error' => 'Insufficient funds', 'code' => 6001]);
        }
    } else {
        echo json_encode(['error' => 'User not found', 'code' => 6005]);
    }
}
?>
