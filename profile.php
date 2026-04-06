<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_login();

$userId = current_user_id();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verify_csrf();

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $phone = trim((string) ($_POST['phone_number'] ?? ''));

    if ($fullName === '') {
        $error = 'Full name is required.';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $error = 'Phone number must start with 09 and contain exactly 11 digits.';
    } else {
        $photo = uploaded_photo_data($_FILES['photo'] ?? null);

        if ($photo !== null) {
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone_number = ?, profile_photo = ? WHERE id = ?');
            $stmt->execute([$fullName, $phone, $photo, $userId]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone_number = ? WHERE id = ?');
            $stmt->execute([$fullName, $phone, $userId]);
        }

        $_SESSION['full_name'] = $fullName;
        app_log('user', 'Profile updated', ['user_id' => $userId]);
        flash('success', 'Profile updated successfully.');
        redirect('profile.php');
    }
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

layout_header('Profile');
?>
<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl border border-stone-200 p-8">
    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700">My Profile</p>
    <h1 class="mt-3 text-4xl font-display"><?= h($user['full_name']) ?></h1>

    <?php if ($error): ?>
        <div class="mt-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="flex flex-col items-center gap-4">
            <img src="<?= h($user['profile_photo'] ?: 'https://picsum.photos/240?grayscale') ?>" alt="Profile photo" class="w-32 h-32 rounded-full object-cover border-4 border-stone-100 shadow-md">
            <input type="file" name="photo" accept="image/png,image/jpeg" class="text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Full name</label>
            <input type="text" name="full_name" value="<?= h($user['full_name']) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Email</label>
            <input type="email" value="<?= h($user['email']) ?>" class="w-full rounded-xl border border-stone-200 bg-stone-100 px-4 py-3" readonly>
        </div>
        <div>
            <label class="block text-sm font-medium mb-2">Phone number</label>
            <input type="tel" name="phone_number" value="<?= h($user['phone_number']) ?>" pattern="09[0-9]{9}" maxlength="11" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
        </div>
        <button type="submit" name="update_profile" class="w-full rounded-xl bg-emerald-800 text-white px-4 py-3 font-semibold">Save changes</button>
    </form>
</div>
<?php layout_footer(); ?>
