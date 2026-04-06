<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone_number'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $photo = null;

    if ($fullName === '') {
        $error = 'Full name is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid email address.';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $error = 'Phone number must start with 09 and contain exactly 11 digits.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $photo = uploaded_photo_data($_FILES['photo'] ?? null);

        $insertStmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, phone_number, password_hash, profile_photo) VALUES (?, ?, ?, ?, ?)'
        );

        try {
            $insertStmt->execute([
                $fullName,
                $email,
                $phone,
                password_hash($password, PASSWORD_BCRYPT),
                $photo,
            ]);

            app_log('auth', 'User registered', ['email' => $email]);
            flash('success', 'Registration complete. You can log in now.');
            redirect('login.php');
        } catch (PDOException $exception) {
            $error = 'Email or phone number is already registered.';
            app_log('auth', 'Registration failed', ['email' => $email, 'error' => $exception->getMessage()]);
        }
    }
}

layout_header('Register');
?>
<div class="max-w-xl mx-auto bg-white rounded-3xl shadow-xl border border-stone-200 p-8">
    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">Create Account</p>
    <h1 class="mt-3 text-4xl font-display">Join the coffee shop community</h1>

    <?php if ($error): ?>
        <div class="mt-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div>
            <label class="block text-sm font-medium mb-2">Full name</label>
            <input type="text" name="full_name" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Phone number</label>
                <input type="tel" name="phone_number" pattern="09[0-9]{9}" maxlength="11" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Password</label>
            <input type="password" name="password" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Profile photo (optional)</label>
            <input type="file" name="photo" accept="image/png,image/jpeg" class="w-full rounded-xl border border-stone-300 px-4 py-3 bg-white">
        </div>
        <button type="submit" class="w-full rounded-xl bg-emerald-800 text-white px-4 py-3 font-semibold">Create account</button>
    </form>

    <p class="mt-5 text-sm text-stone-600">
        Already registered?
        <a href="login.php" class="font-semibold text-emerald-800">Log in here</a>
    </p>
</div>
<?php layout_footer(); ?>
