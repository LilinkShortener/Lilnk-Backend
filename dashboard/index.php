<?php
include '../config.php';

// Fetch summary data
function fetchSummaryData() {
    global $pdo;

    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetchColumn();

    // Total links
    $stmt = $pdo->query("SELECT COUNT(*) AS total_links FROM links");
    $totalLinks = $stmt->fetchColumn();

    // Total withdrawals
    $stmt = $pdo->query("SELECT COUNT(*) AS total_withdrawals FROM withdrawals");
    $totalWithdrawals = $stmt->fetchColumn();

    // Total earnings
    $stmt = $pdo->query("SELECT SUM(total_earnings) AS total_earnings FROM users");
    $totalEarnings = $stmt->fetchColumn();

    return [
        'total_users' => $totalUsers,
        'total_links' => $totalLinks,
        'total_withdrawals' => $totalWithdrawals,
        'total_earnings' => $totalEarnings,
    ];
}

// Fetch server status
function fetchServerStatus() {
    $cpuLoad = shell_exec("top -bn1 | grep 'Cpu(s)' | sed 's/Cpu(s): //g' | awk '{print $1 \"%\"}'");
    $ramUsage = shell_exec("free -m | awk '/^Mem/ { print $3 \" MB / \" $2 \" MB\" }'");

    return [
        'cpu_load' => trim($cpuLoad),
        'ram_usage' => trim($ramUsage),
    ];
}

$page = $_GET['page'] ?? 'dashboard';
$summaryData = fetchSummaryData();
$serverStatus = fetchServerStatus();


// Fetch users, links, and withdrawals for display
function fetchUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchLinks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM links");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchWithdrawals() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM withdrawals");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page = $_GET['page'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت - لیلینک</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazir:wght@400;700&display=swap">
    <link rel="stylesheet" href="styles.css"> <!-- Your custom CSS -->
</head>
<body>
    <header>
        <h1>پنل مدیریت - لیلینک</h1>
        <nav>
            <ul>
                <li><a href="?page=users">مدیریت کاربران</a></li>
                <li><a href="?page=links">مدیریت لینک‌ها</a></li>
                <li><a href="?page=withdrawals">مدیریت برداشت‌ها</a></li>
                <li><a href="/lilnk/dashboard/">خلاصه فعالیت‌ها</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php if ($page === 'users'): ?>
            <h2>مدیریت کاربران</h2>
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>ایمیل</th>
                        <th>کل درآمد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (fetchUsers() as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['total_earnings']); ?></td>
                            <td>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_action" value="edit">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <input type="text" name="email" placeholder="ایمیل جدید" value="<?php echo htmlspecialchars($user['email']); ?>">
                                    <input type="number" step="0.01" name="total_earnings" placeholder="کل درآمد جدید" value="<?php echo htmlspecialchars($user['total_earnings']); ?>">
                                    <button type="submit">ویرایش</button>
                                </form>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($page === 'links'): ?>
            <h2>مدیریت لینک‌ها</h2>
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>لینک کوتاه</th>
                        <th>لینک اصلی</th>
                        <th>تعداد دسترسی</th>
                        <th>تاریخ آخرین دسترسی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (fetchLinks() as $link): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($link['id']); ?></td>
                            <td><?php echo htmlspecialchars($link['short_url']); ?></td>
                            <td><?php echo htmlspecialchars($link['original_url']); ?></td>
                            <td><?php echo htmlspecialchars($link['access_count']); ?></td>
                            <td><?php echo htmlspecialchars($link['last_accessed']); ?></td>
                            <td>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="link_action" value="edit">
                                    <input type="hidden" name="link_id" value="<?php echo htmlspecialchars($link['id']); ?>">
                                    <input type="text" name="short_url" placeholder="لینک کوتاه جدید" value="<?php echo htmlspecialchars($link['short_url']); ?>">
                                    <input type="text" name="original_url" placeholder="لینک اصلی جدید" value="<?php echo htmlspecialchars($link['original_url']); ?>">
                                    <label>
                                        <input type="checkbox" name="with_ads" <?php echo $link['with_ads'] ? 'checked' : ''; ?>>
                                        تبلیغ
                                    </label>
                                    <button type="submit">ویرایش</button>
                                </form>
                                <form action="admin_actions.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="link_action" value="delete">
                                    <input type="hidden" name="link_id" value="<?php echo htmlspecialchars($link['id']); ?>">
                                    <button type="submit">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($page === 'withdrawals'): ?>
            <h2>مدیریت برداشت‌ها</h2>
            <table>
                <thead>
                    <tr>
                        <th>شناسه</th>
                        <th>کاربر</th>
                        <th>شماره IBAN</th>
                        <th>مقدار</th>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>زمان درخواست</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (fetchWithdrawals() as $withdrawal): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($withdrawal['id']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['iban']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['amount']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['name']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['surname']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['request_time']); ?></td>
                            <td><?php echo $withdrawal['status'] ? 'پرداخت شده' : 'در انتظار'; ?></td>
                            <td>
                                <?php if (!$withdrawal['status']): ?>
                                    <a href="admin_actions.php?action=mark_paid&id=<?php echo htmlspecialchars($withdrawal['id']); ?>">علامت‌گذاری به عنوان پرداخت شده</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <h2>خلاصه فعالیت‌ها</h2>
            <div class="summary">
                <p>تعداد کاربران: <?php echo htmlspecialchars($summaryData['total_users']); ?></p>
                <p>تعداد لینک‌ها: <?php echo htmlspecialchars($summaryData['total_links']); ?></p>
                <p>تعداد برداشت‌ها: <?php echo htmlspecialchars($summaryData['total_withdrawals']); ?></p>
                <p>کل درآمدها: <?php echo htmlspecialchars($summaryData['total_earnings']); ?> تومان</p>
            </div>
            <div class="server-status">
                <h3>وضعیت سرور</h3>
                <p>بار CPU: <?php echo htmlspecialchars($serverStatus['cpu_load']); ?></p>
                <p>مصرف RAM: <?php echo htmlspecialchars($serverStatus['ram_usage']); ?></p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 لیلینک - تمامی حقوق محفوظ است.</p>
    </footer>
</body>
</html>
