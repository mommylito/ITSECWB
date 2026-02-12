
<?php
session_start();
require_once 'db_connect.php';

$stmt = $pdo->query("SELECT * FROM menu_items");
$menu = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Green Bean - Menu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900">
    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-stone-200">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-emerald-800 rounded-full flex items-center justify-center text-white font-bold text-lg">G</div>
                <span class="font-serif text-xl tracking-tight text-stone-800">The Green Bean</span>
            </a>
            <div class="flex items-center space-x-6">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="text-sm font-medium text-stone-600 hover:text-emerald-800">Profile</a>
                    <?php if($_SESSION['role'] === 'admin'): ?>
                        <a href="admin.php" class="text-sm font-medium text-stone-600 hover:text-emerald-800">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-sm font-medium text-stone-600">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-sm font-medium text-stone-600">Login</a>
                    <a href="register.php" class="px-4 py-2 bg-emerald-800 text-white text-sm font-medium rounded-full">Join Now</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-12">
        <header class="text-center mb-16">
            <h1 class="text-5xl font-serif text-stone-800">Our Signature Menu</h1>
            <p class="mt-4 text-stone-600 max-w-2xl mx-auto">Freshly roasted, ethically sourced, and securely served.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach($menu as $item): ?>
            <div class="bg-white p-6 rounded-2xl border border-stone-100 shadow-sm flex gap-6">
                <div class="w-24 h-24 bg-stone-50 rounded-xl overflow-hidden">
                    <img src="https://picsum.photos/200/200?coffee=<?= $item['id'] ?>" class="w-full h-full object-cover">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between">
                        <h3 class="text-xl font-bold text-stone-800"><?= htmlspecialchars($item['name']) ?></h3>
                        <span class="text-emerald-800 font-bold">$<?= number_format($item['price'], 2) ?></span>
                    </div>
                    <p class="text-sm text-stone-500 mt-2"><?= htmlspecialchars($item['description']) ?></p>
                    <div class="mt-4 flex justify-between items-center">
                        <span class="text-xs font-bold text-stone-400 uppercase"><?= htmlspecialchars($item['category']) ?></span>
                        <button class="text-sm font-semibold text-emerald-800">Add +</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
