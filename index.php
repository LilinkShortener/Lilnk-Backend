<?php
include 'config.php';

/**
 * Handle short URL requests
 * Redirects to the original URL or displays an ad page based on the `with_ads` flag
 */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestUri = $_SERVER['REQUEST_URI'];
    $urlParts = explode('/', trim($requestUri, '/'));
    $shortUrl = end($urlParts);

    $stmt = $pdo->prepare("SELECT original_url, with_ads, user_id FROM links WHERE short_url = ?");
    $stmt->execute([$shortUrl]);

    if ($stmt->rowCount() > 0) {
        $linkData = $stmt->fetch(PDO::FETCH_ASSOC);
        $originalUrl = $linkData['original_url'];
        $withAds = $linkData['with_ads'];
        $userId = $linkData['user_id'];

        $stmt = $pdo->prepare("UPDATE links SET access_count = access_count + 1, last_accessed = NOW() WHERE short_url = ?");
        $stmt->execute([$shortUrl]);

        if ($withAds) {
            $template = file_get_contents('temp.html');
            $output = str_replace('{{original_url}}', htmlspecialchars($originalUrl), $template);
            echo $output;

            $earningsPerClick = 0.2;
            $stmt = $pdo->prepare("UPDATE links SET earnings = earnings + ? WHERE short_url = ?");
            $stmt->execute([$earningsPerClick, $shortUrl]);

            $stmt = $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?");
            $stmt->execute([$earningsPerClick, $userId]);
        } else {
            header("Location: " . htmlspecialchars($originalUrl));
            exit();
        }
    } else {
        echo "Link not found";
    }
}
?>
