<?php
include '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * Manage notes operations including creating, editing, deleting, and displaying notes.
 * Method: POST
 * 
 * Request Body for creating a note:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "create",
 *     "title": "Note Title",
 *     "content": "Note content here"
 * }
 * 
 * Request Body for editing a note:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "edit",
 *     "id": 1,
 *     "title": "Updated Title",
 *     "content": "Updated content here"
 * }
 * 
 * Request Body for deleting a note:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "delete",
 *     "id": 1
 * }
 * 
 * Request Body for displaying a note:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "display",
 *     "short_url": "abcd1234"
 * }
 * 
 * Request Body for listing all notes for a user:
 * {
 *     "api_key": "your_secret_api_key_here",
 *     "user_id": 1,
 *     "action": "list"
 * }
 * 
 * Response for creating a note:
 * Success: {
 *     "success": true,
 *     "short_url": "abcd1234"
 * }
 * 
 * Response for editing a note:
 * Success: {
 *     "success": true,
 *     "short_url": "abcd1234"
 * }
 * 
 * Response for deleting a note:
 * Success: {
 *     "success": true
 * }
 * 
 * Response for displaying a note:
 * Success: {
 *     "id": 1,
 *     "user_id": 1,
 *     "title": "Note Title",
 *     "content": "Note content here",
 *     "short_url": "abcd1234",
 *     "created_at": "2024-08-01 12:00:00",
 *     "updated_at": "2024-08-02 12:00:00",
 *     "access_count": 10,
 *     "last_accessed": "2024-08-03 12:00:00",
 *     "email": "user@example.com"
 * }
 * 
 * Response for listing notes:
 * Success: {
 *     "success": true,
 *     "notes": [
 *         {
 *             "id": 1,
 *             "title": "First Note",
 *             "content": "First note content",
 *             "short_url": "abcd1234",
 *             "created_at": "2024-08-01 12:00:00"
 *         },
 *         ...
 *     ]
 * }
 * 
 * Error:
 * - {"error": "Invalid API Key", "code": 9000} - API Key اشتباه است.
 * - {"error": "Failed to create note", "code": 9001} - ایجاد نوت با خطا مواجه شد.
 * - {"error": "Failed to update note", "code": 9002} - ویرایش نوت با خطا مواجه شد.
 * - {"error": "Failed to delete note", "code": 9003} - حذف نوت با خطا مواجه شد.
 * - {"error": "Invalid short URL", "code": 9004} - آدرس کوتاه نوت نادرست است.
 * - {"error": "Note not found", "code": 9005} - نوت پیدا نشد.
 * - {"error": "No notes found for this user", "code": 9006} - نوتی برای این کاربر پیدا نشد.
 * - {"error": "Invalid action", "code": 9007} - اکشن نادرست است.
 */

$data = json_decode(file_get_contents('php://input'), true);

// بررسی کلید API
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 9000]);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($data['action']) ? $data['action'] : '';

switch ($action) {
    // ایجاد نوت
    case 'create':
        $title = $data['title'] ?? null;
        $content = $data['content'];
        $userId = $data['user_id'];
        $shortUrl = bin2hex(random_bytes(4));

        $stmt = $pdo->prepare("INSERT INTO notes (title, content, short_url, user_id) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $content, $shortUrl, $userId])) {
            echo json_encode(['success' => true, 'short_url' => $shortUrl]);
        } else {
            echo json_encode(['error' => 'Failed to create note', 'code' => 9001]);
        }
        break;

    // ویرایش نوت
    case 'edit':
        $id = $data['id'];
        $title = $data['title'] ?? null;
        $content = $data['content'];
    
        // ویرایش نوت
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if ($stmt->execute([$title, $content, $id])) {
            // دریافت short_url بعد از ویرایش
            $stmt = $pdo->prepare("SELECT short_url FROM notes WHERE id = ?");
            $stmt->execute([$id]);
            $shortUrl = $stmt->fetchColumn();
    
            echo json_encode(['success' => true, 'short_url' => $shortUrl]);
        } else {
            echo json_encode(['error' => 'Failed to update note', 'code' => 9002]);
        }
        break;

    // حذف نوت
    case 'delete':
        $id = $data['id'];

        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to delete note', 'code' => 9003]);
        }
        break;

    // نمایش نوت
    case 'display':
        $shortUrl = $data['short_url'] ?? '';

        // بررسی موجود بودن short_url
        if (empty($shortUrl)) {
            echo json_encode(['error' => 'Invalid short URL!', 'code' => 9004]);
            exit();
        }

        // بازیابی نوت از دیتابیس
        $stmt = $pdo->prepare("SELECT notes.*, users.email FROM notes JOIN users ON notes.user_id = users.id WHERE short_url = ?");
        $stmt->execute([$shortUrl]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($note) {
            echo json_encode($note);
        } else {
            echo json_encode(['error' => 'Note not found', 'code' => 9005]);
        }
        break;

    // دریافت لیست نوت‌ها برای یک کاربر
    case 'list':
        $userId = $data['user_id'];

        $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ?");
        $stmt->execute([$userId]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($notes) {
            echo json_encode(['success' => true, 'notes' => $notes]);
        } else {
            echo json_encode(['error' => 'No notes found for this user', 'code' => 9006]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action', 'code' => 9007]);
}
}
?>
