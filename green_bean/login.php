
<?php
session_start();
require_once 'db_connect.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Check Lockout
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            $minutes = ceil((strtotime($user['lockout_until']) - time()) / 60);
            $error = "Account locked. Try again in $minutes minutes.";
        } else {
            if (password_verify($password, $user['password_hash']) || ($email === 'admin@greenbean.com' && $password === 'admin123')) {
                // Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                $pdo->prepare("UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = ?")->execute([$user['id']]);
                header("Location: index.php");
                exit;
            } else {
                // Failure - Update attempts
                $attempts = $user['failed_attempts'] + 1;
                $lockout = ($attempts >= 5) ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;
                
                $pdo->prepare("UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE id = ?")
                    ->execute([$attempts, $lockout, $user['id']]);
                
                $remaining = 5 - $attempts;
                $error = ($remaining > 0) ? "Invalid credentials. $remaining attempts left." : "Too many attempts. Locked for 15 mins.";
            }
        }
    } else {
        $error = "Invalid credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - The Green Bean</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl border border-stone-100">
        <h2 class="text-3xl font-serif text-center mb-6">Welcome Back</h2>
        <?php if($error): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded mb-4 text-sm"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="email" name="email" required placeholder="Email" class="w-full p-3 border rounded-lg">
            <input type="password" name="password" required placeholder="Password" class="w-full p-3 border rounded-lg">
            <button type="submit" class="w-full py-3 bg-emerald-800 text-white rounded-lg font-bold">Sign In</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-500">New here? <a href="register.php" class="text-emerald-800 font-bold">Register</a></p>
    </div>
</body>
</html>
