<?php
include 'config.php';

/**
 * Handle short URL requests
 * Redirects to the original URL or displays an ad page based on the `with_ads` flag
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Extract the short URL part from the request URI
    $urlParts = explode('/', trim($requestUri, '/'));
    $shortUrl = end($urlParts);

    // Retrieve link details based on the short URL
    $stmt = $pdo->prepare("SELECT original_url, with_ads, user_id FROM links WHERE short_url = ?");
    $stmt->execute([$shortUrl]);

    if ($stmt->rowCount() > 0) {
        $linkData = $stmt->fetch(PDO::FETCH_ASSOC);
        $originalUrl = $linkData['original_url'];
        $withAds = $linkData['with_ads'];
        $userId = $linkData['user_id'];

        // Update access count and last accessed time
        $stmt = $pdo->prepare("UPDATE links SET access_count = access_count + 1, last_accessed = NOW() WHERE short_url = ?");
        $stmt->execute([$shortUrl]);

        if ($withAds) {
            // Load the HTML template and replace the placeholder with the actual URL
            $template = file_get_contents('temp.html');
            $output = str_replace('{{original_url}}', htmlspecialchars($originalUrl), $template);
            echo $output;

            // Update earnings for the link
            $earningsPerClick = 0.2; // Example value
            $stmt = $pdo->prepare("UPDATE links SET earnings = earnings + ? WHERE short_url = ?");
            $stmt->execute([$earningsPerClick, $shortUrl]);

            // Update total earnings for the user
            $stmt = $pdo->prepare("UPDATE users SET total_earnings = total_earnings + ? WHERE id = ?");
            $stmt->execute([$earningsPerClick, $userId]);
        } else {
            // Redirect to the original URL
            header("Location: " . htmlspecialchars($originalUrl));
            exit();
        }
    } else {
        echo "Link not found";
    }
}
?>
