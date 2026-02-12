
<?php
require_once 'db_connect.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } elseif (strlen($pass) < 8) {
        $err = "Password must be at least 8 characters.";
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $err = "Email already registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join The Green Bean</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl">
        <h2 class="text-3xl font-serif text-center mb-6">Create Account</h2>
        <?php if($err): ?> <div class="p-3 bg-red-50 text-red-700 mb-4"><?= $err ?></div> <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="text" name="full_name" required placeholder="Full Name" class="w-full p-3 border rounded-lg">
            <input type="email" name="email" required placeholder="Email" class="w-full p-3 border rounded-lg">
            <input type="password" name="password" required placeholder="Password (Min 8 chars)" class="w-full p-3 border rounded-lg">
            <button type="submit" class="w-full py-3 bg-emerald-800 text-white rounded-lg font-bold">Join Community</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-500">Member already? <a href="login.php" class="text-emerald-800 font-bold">Login</a></p>
    </div>
</body>
</html>
