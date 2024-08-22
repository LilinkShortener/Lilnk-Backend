<?php
include '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

// بررسی کلید API
if (!isset($data['api_key']) || $data['api_key'] !== API_KEY) {
    echo json_encode(['error' => 'Invalid API Key', 'code' => 9000]);
    exit();
}

// انتخاب عملیات بر اساس action
$action = $data['action'] ?? '';

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

?>
