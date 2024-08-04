<?php
include '../config.php';

// Function to handle user actions
function handleUserActions($action, $userId, $email = null, $totalEarnings = null) {
    global $pdo;
    
    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, total_earnings = ? WHERE id = ?");
        return $stmt->execute([$email, $totalEarnings, $userId]);
    } elseif ($action === 'delete') {
        // Delete all links for the user before deleting the user
        $stmt = $pdo->prepare("DELETE FROM links WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}

// Function to handle link actions
function handleLinkActions($action, $linkId, $shortUrl = null, $originalUrl = null, $withAds = null) {
    global $pdo;

    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE links SET short_url = ?, original_url = ?, with_ads = ? WHERE id = ?");
        return $stmt->execute([$shortUrl, $originalUrl, $withAds, $linkId]);
    } elseif ($action === 'delete') {
        return $pdo->prepare("DELETE FROM links WHERE id = ?")->execute([$linkId]);
    }
}

// Function to handle withdrawal actions
function handleWithdrawalActions($action, $withdrawalId) {
    global $pdo;

    if ($action === 'mark_paid') {
        $stmt = $pdo->prepare("UPDATE withdrawals SET status = 1 WHERE id = ?");
        return $stmt->execute([$withdrawalId]);
    }
}

// Process actions based on request
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    if (isset($_POST['user_action'])) {
        $action = $_POST['user_action'];
        $userId = $_POST['user_id'];
        $email = $_POST['email'] ?? null;
        $totalEarnings = $_POST['total_earnings'] ?? null;
        
        if (handleUserActions($action, $userId, $email, $totalEarnings)) {
            header("Location: index.php?page=users");
        } else {
            echo "Error handling user action.";
        }
    } elseif (isset($_POST['link_action'])) {
        $action = $_POST['link_action'];
        $linkId = $_POST['link_id'];
        $shortUrl = $_POST['short_url'] ?? null;
        $originalUrl = $_POST['original_url'] ?? null;
        $withAds = isset($_POST['with_ads']) ? 1 : 0;

        if (handleLinkActions($action, $linkId, $shortUrl, $originalUrl, $withAds)) {
            header("Location: index.php?page=links");
        } else {
            echo "Error handling link action.";
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'mark_paid') {
        $withdrawalId = $_GET['id'];
        if (handleWithdrawalActions('mark_paid', $withdrawalId)) {
            header("Location: index.php?page=withdrawals");
        } else {
            echo "Error marking withdrawal as paid.";
        }
    }
} else {
    echo "Invalid request.";
}
?>
