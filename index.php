<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    require_login();
    verify_csrf();

    $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);

    if ($menuItemId <= 0 || $quantity <= 0) {
        throw new RuntimeException('Invalid order request.');
    }

    $pdo->beginTransaction();

    try {
        $itemStmt = $pdo->prepare(
            'SELECT menu_items.*, categories.name AS category_name
             FROM menu_items
             INNER JOIN categories ON categories.id = menu_items.category_id
             WHERE menu_items.id = ? FOR UPDATE'
        );
        $itemStmt->execute([$menuItemId]);
        $item = $itemStmt->fetch();

        if (!$item) {
            throw new RuntimeException('Menu item not found.');
        }

        if ((int) $item['stock'] < $quantity) {
            throw new RuntimeException('Not enough stock available for that order.');
        }

        $total = (float) $item['price'] * $quantity;

        $orderStmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)');
        $orderStmt->execute([current_user_id(), $total, 'pending']);
        $orderId = (int) $pdo->lastInsertId();

        $orderItemStmt = $pdo->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)');
        $orderItemStmt->execute([$orderId, $menuItemId, $quantity, $item['price']]);

        $stockStmt = $pdo->prepare('UPDATE menu_items SET stock = stock - ? WHERE id = ?');
        $stockStmt->execute([$quantity, $menuItemId]);

        $pdo->commit();

        app_log('transaction', 'Order created', [
            'user_id' => current_user_id(),
            'order_id' => $orderId,
            'menu_item_id' => $menuItemId,
            'quantity' => $quantity,
            'total' => $total,
        ]);

        flash('success', 'Order placed successfully.');
        redirect('index.php');
    } catch (Throwable $exception) {
        $pdo->rollBack();
        app_log('transaction', 'Order failed', [
            'user_id' => current_user_id(),
            'menu_item_id' => $menuItemId,
            'quantity' => $quantity,
            'error' => $exception->getMessage(),
        ]);
        throw $exception;
    }
}

$menuStmt = $pdo->query(
    'SELECT menu_items.*, categories.name AS category_name
     FROM menu_items
     INNER JOIN categories ON categories.id = menu_items.category_id
     ORDER BY menu_items.created_at DESC'
);
$menuItems = $menuStmt->fetchAll();

$reviewStmt = $pdo->query(
    'SELECT cafe_reviews.*, users.full_name
     FROM cafe_reviews
     INNER JOIN users ON users.id = cafe_reviews.user_id
     ORDER BY cafe_reviews.created_at DESC
     LIMIT 6'
);
$recentReviews = $reviewStmt->fetchAll();

layout_header('Home');
?>
<section>
    <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
        <div class="grid sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-stone-100 p-5">
                <div class="text-3xl font-bold text-stone-900"><?= count($menuItems) ?></div>
                <div class="text-sm text-stone-600 mt-1">Menu items</div>
            </div>
            <div class="rounded-2xl bg-stone-100 p-5">
                <div class="text-3xl font-bold text-stone-900"><?= count($recentReviews) ?></div>
                <div class="text-sm text-stone-600 mt-1">Recent customer reviews</div>
            </div>
            <div class="rounded-2xl bg-stone-100 p-5">
                <div class="text-3xl font-bold text-stone-900">15m</div>
                <div class="text-sm text-stone-600 mt-1">Session timeout window</div>
            </div>
        </div>
    </div>
</section>

<section class="mt-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-display">Cafe menu</h2>
            <p class="text-stone-600">Order any item below. Logged-in users can submit pending orders.</p>
        </div>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
        <?php foreach ($menuItems as $item): ?>
            <article class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500"><?= h($item['category_name']) ?></p>
                        <h3 class="mt-2 text-2xl font-semibold text-stone-900"><?= h($item['name']) ?></h3>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-emerald-800">PHP <?= number_format((float) $item['price'], 2) ?></div>
                        <div class="text-xs text-stone-500 mt-1">Stock: <?= (int) $item['stock'] ?></div>
                    </div>
                </div>
                <p class="mt-4 text-stone-600 leading-7"><?= h($item['description']) ?></p>

                <?php if (is_logged_in()): ?>
                    <form method="POST" class="mt-6 flex flex-col sm:flex-row gap-3">
                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="menu_item_id" value="<?= (int) $item['id'] ?>">
                        <input type="number" name="quantity" min="1" max="<?= (int) $item['stock'] ?>" value="1" class="w-full sm:w-28 rounded-xl border border-stone-300 px-4 py-3" required>
                        <button type="submit" name="place_order" class="rounded-xl bg-emerald-800 text-white px-5 py-3 font-semibold disabled:opacity-50" <?= (int) $item['stock'] === 0 ? 'disabled' : '' ?>>
                            <?= (int) $item['stock'] === 0 ? 'Out of stock' : 'Place order' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <p class="mt-6 text-sm text-stone-500">Log in to place an order.</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="mt-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-display">Latest customer reviews</h2>
            <p class="text-stone-600">Customer feedback is displayed here with the numeric details attached to each review.</p>
        </div>
        <?php if (is_logged_in()): ?>
            <a href="reviews.php" class="text-emerald-800 font-semibold">Manage my reviews</a>
        <?php endif; ?>
    </div>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($recentReviews as $review): ?>
            <article class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm h-full flex flex-col">
                <p class="text-xs uppercase tracking-[0.2em] text-stone-500"><?= h($review['full_name']) ?></p>
                <h3 class="mt-2 text-xl font-semibold"><?= h($review['title']) ?></h3>
                <p class="mt-3 text-stone-600 leading-7"><?= nl2br(h($review['notes'])) ?></p>
                <div class="mt-auto pt-5 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-2xl bg-stone-100 p-3 min-h-24 flex flex-col items-center">
                        <div class="min-h-10 flex items-center justify-center font-bold text-2xl leading-none"><?= (int) $review['cups_count'] ?></div>
                        <div class="mt-auto text-xs text-stone-500">Cups</div>
                    </div>
                    <div class="rounded-2xl bg-stone-100 p-3 min-h-24 flex flex-col items-center">
                        <div class="min-h-10 flex items-center justify-center font-bold text-2xl leading-none"><?= number_format((float) $review['spending_amount'], 2) ?></div>
                        <div class="mt-auto text-xs text-stone-500">Spent (PHP)</div>
                    </div>
                    <div class="rounded-2xl bg-stone-100 p-3 min-h-24 flex flex-col items-center">
                        <div class="min-h-10 flex items-center justify-center font-bold text-2xl leading-none"><?= (int) $review['sweetness_level'] ?>/5</div>
                        <div class="mt-auto text-xs text-stone-500">Rating</div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php layout_footer(); ?>
