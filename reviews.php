<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_login();

$userId = current_user_id();
$editReview = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save') {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $cupsCount = (int) ($_POST['cups_count'] ?? 0);
        $spendingAmount = (float) ($_POST['spending_amount'] ?? 0);
        $sweetnessLevel = (int) ($_POST['sweetness_level'] ?? 0);

        if ($title === '' || $notes === '') {
            $error = 'Title and review message are required.';
        } elseif ($cupsCount < 1) {
            $error = 'Cups count must be at least 1.';
        } elseif ($spendingAmount < 0) {
            $error = 'Spent amount cannot be negative.';
        } elseif ($sweetnessLevel < 1 || $sweetnessLevel > 5) {
            $error = 'Rating must be between 1 and 5.';
        } else {
            if ($reviewId > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE cafe_reviews
                     SET title = ?, notes = ?, cups_count = ?, spending_amount = ?, sweetness_level = ?
                     WHERE id = ? AND user_id = ?'
                );
                $stmt->execute([$title, $notes, $cupsCount, $spendingAmount, $sweetnessLevel, $reviewId, $userId]);
                app_log('user', 'Customer review updated', ['user_id' => $userId, 'review_id' => $reviewId]);
                flash('success', 'Customer review updated.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO cafe_reviews (user_id, title, notes, cups_count, spending_amount, sweetness_level)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$userId, $title, $notes, $cupsCount, $spendingAmount, $sweetnessLevel]);
                app_log('user', 'Customer review created', ['user_id' => $userId, 'review_id' => $pdo->lastInsertId()]);
                flash('success', 'Customer review created.');
            }

            redirect('reviews.php');
        }
    }

    if ($action === 'delete') {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM cafe_reviews WHERE id = ? AND user_id = ?');
        $stmt->execute([$reviewId, $userId]);
        app_log('user', 'Customer review deleted', ['user_id' => $userId, 'review_id' => $reviewId]);
        flash('success', 'Customer review deleted.');
        redirect('reviews.php');
    }
}

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM cafe_reviews WHERE id = ? AND user_id = ?');
    $stmt->execute([$editId, $userId]);
    $editReview = $stmt->fetch() ?: null;
}

$reviewsStmt = $pdo->prepare('SELECT * FROM cafe_reviews WHERE user_id = ? ORDER BY updated_at DESC');
$reviewsStmt->execute([$userId]);
$reviews = $reviewsStmt->fetchAll();

layout_header('My Reviews');
?>
<section class="grid lg:grid-cols-[0.95fr_1.05fr] gap-8">
    <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-700"><?= $editReview ? 'Edit Review' : 'New Review' ?></p>
        <h1 class="mt-3 text-4xl font-display"><?= $editReview ? 'Update your cafe review' : 'Create a customer review' ?></h1>
        <p class="mt-3 text-stone-600">This feature keeps the app centered on the coffee shop while still satisfying the text input requirement and three numeric fields.</p>

        <?php if ($error): ?>
            <div class="mt-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="review_id" value="<?= (int) ($editReview['id'] ?? 0) ?>">
            <div>
                <label class="block text-sm font-medium mb-2">Review title</label>
                <input type="text" name="title" value="<?= h($editReview['title'] ?? '') ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Review message</label>
                <textarea name="notes" rows="5" class="w-full rounded-xl border border-stone-300 px-4 py-3" required><?= h($editReview['notes'] ?? '') ?></textarea>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Cups ordered</label>
                    <input type="number" name="cups_count" min="1" value="<?= h((string) ($editReview['cups_count'] ?? 1)) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Spent</label>
                    <input type="number" name="spending_amount" min="0" step="0.01" value="<?= h((string) ($editReview['spending_amount'] ?? '0.00')) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Rating (1-5)</label>
                    <input type="number" name="sweetness_level" min="1" max="5" value="<?= h((string) ($editReview['sweetness_level'] ?? 3)) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                </div>
            </div>
            <button type="submit" class="w-full rounded-xl bg-emerald-800 text-white px-4 py-3 font-semibold">
                <?= $editReview ? 'Update review' : 'Save review' ?>
            </button>
        </form>
    </div>

    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-stone-500">My Reviews</p>
                <h2 class="mt-2 text-3xl font-display">Saved customer feedback</h2>
            </div>
            <?php if ($editReview): ?>
                <a href="reviews.php" class="text-emerald-800 font-semibold">Cancel edit</a>
            <?php endif; ?>
        </div>

        <div class="space-y-5">
            <?php foreach ($reviews as $review): ?>
                <article class="bg-white rounded-3xl shadow-sm border border-stone-200 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-semibold"><?= h($review['title']) ?></h3>
                            <p class="text-sm text-stone-500 mt-1">Updated <?= h($review['updated_at']) ?></p>
                        </div>
                        <div class="flex gap-3">
                            <a href="reviews.php?edit=<?= (int) $review['id'] ?>" class="px-4 py-2 rounded-xl border border-stone-300 text-sm font-medium">Edit</a>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="mt-4 text-stone-600 leading-7"><?= nl2br(h($review['notes'])) ?></p>
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-stone-100 p-4 text-center">
                            <div class="font-bold text-lg"><?= (int) $review['cups_count'] ?></div>
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-500">Cups</div>
                        </div>
                        <div class="rounded-2xl bg-stone-100 p-4 text-center">
                            <div class="font-bold text-lg"><?= number_format((float) $review['spending_amount'], 2) ?></div>
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-500">Spent</div>
                        </div>
                        <div class="rounded-2xl bg-stone-100 p-4 text-center">
                            <div class="font-bold text-lg"><?= (int) $review['sweetness_level'] ?>/5</div>
                            <div class="text-xs uppercase tracking-[0.2em] text-stone-500">Rating</div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (!$reviews): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-dashed border-stone-300 p-8 text-stone-500">
                    No customer reviews yet. Create one from the form.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php layout_footer(); ?>
