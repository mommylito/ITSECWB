
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['full_name'];
        
        // Handle File Upload with Security
        if (!empty($_FILES['photo']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);

            if (in_array($mime, ['image/jpeg', 'image/png'])) {
                $data = file_get_contents($_FILES['photo']['tmp_name']);
                $base64 = 'data:' . $mime . ';base64,' . base64_encode($data);
                $pdo->prepare("UPDATE users SET full_name = ?, profile_photo = ? WHERE id = ?")->execute([$name, $base64, $user_id]);
                $msg = "Profile updated!";
            } else {
                $err = "Invalid file type. Only JPG/PNG allowed.";
            }
        } else {
            $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?")->execute([$name, $user_id]);
            $msg = "Name updated!";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Green Bean</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-50">
    <div class="max-w-2xl mx-auto py-12">
        <div class="bg-white p-10 rounded-3xl shadow-xl">
            <h2 class="text-3xl font-serif mb-6">My Profile</h2>
            <?php if($msg): ?> <div class="p-3 bg-green-50 text-green-700 rounded mb-4"><?= $msg ?></div> <?php endif; ?>
            <?php if($err): ?> <div class="p-3 bg-red-50 text-red-700 rounded mb-4"><?= $err ?></div> <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="flex flex-col items-center mb-8">
                    <img src="<?= $user['profile_photo'] ?: 'https://picsum.photos/200' ?>" class="w-32 h-32 rounded-full border-4 border-stone-100 shadow-md object-cover">
                    <input type="file" name="photo" class="mt-4 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-stone-400">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="w-full p-3 border rounded-lg mt-1">
                </div>
                <button type="submit" name="update_profile" class="w-full py-3 bg-emerald-800 text-white rounded-xl font-bold">Save Changes</button>
            </form>
            <a href="index.php" class="block text-center mt-6 text-stone-500">Back to Menu</a>
        </div>
    </div>
</body>
</html>
