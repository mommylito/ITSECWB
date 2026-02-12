
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: Baristas Only.");
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Vault - Green Bean</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-serif mb-8">Admin Vault</h1>
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-stone-100 text-xs font-bold uppercase text-stone-500">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Security Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td class="px-6 py-4 flex items-center gap-3">
                            <img src="<?= $u['profile_photo'] ?: 'https://picsum.photos/40' ?>" class="w-10 h-10 rounded-full">
                            <div>
                                <div class="font-bold"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div class="text-xs text-stone-400"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?= $u['role'] === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($u['lockout_until']): ?>
                                <span class="text-red-600 font-bold text-xs">LOCKED</span>
                            <?php else: ?>
                                <span class="text-green-600 font-bold text-xs">SECURE</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="inline-block mt-8 text-emerald-800 font-bold">← Back to Menu</a>
    </div>
</body>
</html>
