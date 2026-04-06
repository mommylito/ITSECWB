<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'Invalid credentials.';
        app_log('auth', 'Login failed for unknown email', ['email' => $email]);
    } elseif ($user['lockout_until'] && strtotime((string) $user['lockout_until']) > time()) {
        $minutes = (int) ceil((strtotime((string) $user['lockout_until']) - time()) / 60);
        $error = 'Account locked. Try again in ' . $minutes . ' minute(s).';
        app_log('auth', 'Login blocked by lockout', ['user_id' => $user['id']]);
    } elseif (password_verify($password, (string) $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['last_activity'] = time();

        $resetStmt = $pdo->prepare('UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = ?');
        $resetStmt->execute([$user['id']]);

        app_log('auth', 'Login successful', ['user_id' => $user['id'], 'role' => $user['role']]);
        flash('success', 'Welcome back, ' . $user['full_name'] . '.');
        redirect('index.php');
    } else {
        $attempts = (int) $user['failed_attempts'] + 1;
        $lockoutUntil = $attempts >= 5 ? date('Y-m-d H:i:s', strtotime('+15 minutes')) : null;

        $updateStmt = $pdo->prepare('UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE id = ?');
        $updateStmt->execute([$attempts, $lockoutUntil, $user['id']]);

        $remaining = max(0, 5 - $attempts);
        $error = $remaining > 0
            ? 'Invalid credentials. ' . $remaining . ' attempt(s) left before lockout.'
            : 'Too many failed attempts. Account locked for 15 minutes.';

        app_log('auth', 'Login failed with bad password', [
            'user_id' => $user['id'],
            'attempts' => $attempts,
            'lockout_until' => $lockoutUntil,
        ]);
    }
}

layout_header('Login');
?>
<div class="max-w-md mx-auto bg-white rounded-3xl shadow-xl border border-stone-200 p-8">
    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Authentication</p>
    <h1 class="mt-3 text-4xl font-display">Sign in</h1>
    <p class="mt-3 text-stone-600">Sessions expire after 15 minutes of inactivity for milestone compliance.</p>

    <?php if ($error): ?>
        <div class="mt-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <input type="email" name="email" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Password</label>
            <input type="password" name="password" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <button type="submit" class="w-full rounded-xl bg-emerald-800 text-white px-4 py-3 font-semibold">Log in</button>
    </form>

    <p class="mt-5 text-sm text-stone-600">
        Need an account?
        <a href="register.php" class="font-semibold text-emerald-800">Register here</a>
    </p>
</div>
<?php layout_footer(); ?>
