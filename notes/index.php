<?php
include '../config.php';

// تغییر فرمت تاریخ به فرمت خوانا
function formatDate($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('d F Y, H:i:s');
}

// نمایش نوت
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $shortUrl = $_GET['short_url'] ?? '';

    // بررسی موجود بودن short_url
    if (empty($shortUrl)) {
        echo "Invalid short URL!";
        exit();
    }

    // شروع تراکنش
    $pdo->beginTransaction();

    // بازیابی نوت از دیتابیس
    $stmt = $pdo->prepare("SELECT notes.*, users.email FROM notes JOIN users ON notes.user_id = users.id WHERE short_url = ?");
    $stmt->execute([$shortUrl]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    // بررسی موجود بودن نوت
    if (!$note) {
        echo "Note not found!";
        $pdo->rollBack();
        exit();
    }

    // بروزرسانی تعداد بازدید و آخرین دسترسی
    $updateStmt = $pdo->prepare("UPDATE notes SET access_count = access_count + 1, last_accessed = CURRENT_TIMESTAMP WHERE short_url = ?");
    if (!$updateStmt->execute([$shortUrl])) {
        echo "Error updating access count: " . implode(", ", $updateStmt->errorInfo());
        $pdo->rollBack();
        exit();
    }

    // انجام commit تراکنش
    $pdo->commit();

    // نمایش نوت در صفحه HTML
    ?>
    <!DOCTYPE html>
    <html lang="fa">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($note['title'] ?? 'نوت بدون عنوان'); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Vazir:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Vazir', sans-serif;
                background-color: #121212;
                color: #e0e0e0;
                margin: 0;
                padding: 0;
                direction: rtl;
            }
            .container {
                width: 90%;
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                background-color: #1e1e1e;
                border-radius: 8px;
                color: #e0e0e0;
            }
            h1 {
                color: #f39c12;
            }
            .note-content {
                padding: 10px;
                background-color: #2c2c2c;
                border-radius: 4px;
                margin-top: 10px;
                line-height: 1.8;
                font-size: 1.1rem;
                word-wrap: break-word;
            }
            .footer {
                margin-top: 20px;
                font-size: 0.9em;
                color: #b0b0b0;
            }
            .footer span {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?php echo htmlspecialchars($note['title'] ?? 'نوت بدون عنوان'); ?></h1>
            <div class="note-content">
                <?php echo nl2br(htmlspecialchars($note['content'])); ?>
            </div>
            <div class="footer">
                <p><span>تاریخ ایجاد:</span> <?php echo formatDate($note['created_at']); ?></p>
                <p><span>آخرین بروزرسانی:</span> <?php echo $note['updated_at'] ? formatDate($note['updated_at']) : 'بدون بروزرسانی'; ?></p>
                <p><span>ایمیل سازنده:</span> <?php echo htmlspecialchars($note['email']); ?></p>
                <p><span>تعداد بازدید:</span> <?php echo htmlspecialchars($note['access_count'] + 1); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
