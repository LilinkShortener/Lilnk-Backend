<?php
include 'config.php';

/**
 * Withdraw API
 * Method: POST
 * Request Body for processing withdrawal:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "action": "process",
 *     "user_id": 1,
 *     "iban": "IR123456789012345678901234567890",
 *     "name": "John",
 *     "surname": "Doe"
 * }
 * Response for processing:
 * Success: {"success": true, "message": "Withdrawal processed successfully"}
 * Error: {"error": "Insufficient funds"}
 * 
 * Request Body for listing withdrawals:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "action": "list",
 *     "user_id": 1
 * }
 * Response for listing:
 * Success: {
 *     "user_id": 1,
 *     "withdrawals": [
 *         {
 *             "id": 1,
 *             "user_id": 1,
 *             "iban": "IR123456789012345678901234567890",
 *             "amount": 100.00,
 *             "name": "John",
 *             "surname": "Doe",
 *             "request_time": "2024-08-02 12:00:00"
 *         },
 *         ...
 *     ]
 * }
 */

$data = json_decode(file_get_contents('php://input'), true);

// Check API Key
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'process':
                processWithdrawal($data);
                break;
            case 'list':
                listWithdrawals($data);
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['error' => 'No action specified']);
    }
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
                echo json_encode(['error' => 'Failed to process withdrawal']);
            }
        } else {
            echo json_encode(['error' => 'Insufficient funds']);
        }
    } else {
        echo json_encode(['error' => 'User not found']);
    }
}

function listWithdrawals($data) {
    global $pdo;

    $userId = $data['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ?");
    $stmt->execute([$userId]);
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['user_id' => $userId, 'withdrawals' => $withdrawals]);
}
?>
