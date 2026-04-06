<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

require_admin();

$error = null;
$section = (string) ($_GET['section'] ?? '');
$allowedSections = ['menu', 'users', 'orders'];
if ($section !== '' && !in_array($section, $allowedSections, true)) {
    $section = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'create_menu' || $action === 'update_menu') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $price = (float) ($_POST['price'] ?? 0);
            $stock = (int) ($_POST['stock'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);

            if ($name === '' || $description === '') {
                throw new RuntimeException('Name and description are required.');
            }

            if ($price < 0 || $stock < 0 || $categoryId <= 0) {
                throw new RuntimeException('Price, stock, and category must be valid.');
            }

            if ($action === 'create_menu') {
                $stmt = $pdo->prepare('INSERT INTO menu_items (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $description, $price, $stock, $categoryId]);
                app_log('admin', 'Menu item created', ['admin_id' => current_user_id(), 'menu_item_id' => $pdo->lastInsertId()]);
                flash('success', 'Menu item created.');
            } else {
                $stmt = $pdo->prepare('UPDATE menu_items SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $stock, $categoryId, $menuId]);
                app_log('admin', 'Menu item updated', ['admin_id' => current_user_id(), 'menu_item_id' => $menuId]);
                flash('success', 'Menu item updated.');
            }

            redirect('admin.php?section=menu');
        }

        if ($action === 'delete_menu') {
            $menuId = (int) ($_POST['menu_id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
            $stmt->execute([$menuId]);
            app_log('admin', 'Menu item deleted', ['admin_id' => current_user_id(), 'menu_item_id' => $menuId]);
            flash('success', 'Menu item deleted.');
            redirect('admin.php?section=menu');
        }

        if ($action === 'unlock_user') {
            $targetUserId = (int) ($_POST['target_user_id'] ?? 0);
            $stmt = $pdo->prepare('UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = ?');
            $stmt->execute([$targetUserId]);
            app_log('admin', 'User unlocked', ['admin_id' => current_user_id(), 'target_user_id' => $targetUserId]);
            flash('success', 'User lockout cleared.');
            redirect('admin.php?section=users');
        }

        if ($action === 'lock_user') {
            $targetUserId = (int) ($_POST['target_user_id'] ?? 0);
            $lockoutUntil = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $stmt = $pdo->prepare('UPDATE users SET failed_attempts = 5, lockout_until = ? WHERE id = ?');
            $stmt->execute([$lockoutUntil, $targetUserId]);
            app_log('admin', 'User locked', [
                'admin_id' => current_user_id(),
                'target_user_id' => $targetUserId,
                'lockout_until' => $lockoutUntil,
            ]);
            flash('success', 'User locked for 15 minutes.');
            redirect('admin.php?section=users');
        }

        if ($action === 'update_order_status') {
            $orderId = (int) ($_POST['order_id'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'pending');
            if (!in_array($status, ['pending', 'completed', 'cancelled'], true)) {
                throw new RuntimeException('Invalid order status.');
            }

            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, $orderId]);
            app_log('admin', 'Order status updated', ['admin_id' => current_user_id(), 'order_id' => $orderId, 'status' => $status]);
            flash('success', 'Order status updated.');
            redirect('admin.php?section=orders');
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$menuItems = $pdo->query(
    'SELECT menu_items.*, categories.name AS category_name
     FROM menu_items
     INNER JOIN categories ON categories.id = menu_items.category_id
     ORDER BY menu_items.updated_at DESC'
)->fetchAll();
$orders = $pdo->query(
    'SELECT orders.*, users.full_name
     FROM orders
     INNER JOIN users ON users.id = orders.user_id
     ORDER BY orders.created_at DESC'
)->fetchAll();

$editMenu = null;
if ($section === 'menu' && isset($_GET['edit_menu'])) {
    $menuId = (int) $_GET['edit_menu'];
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$menuId]);
    $editMenu = $stmt->fetch() ?: null;
}

layout_header('Admin');
?>
<?php if ($section === ''): ?>
    <section class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-700">Admin Dashboard</p>
        <h1 class="mt-3 text-4xl font-display">Choose an admin task</h1>
        <p class="mt-3 text-stone-600">Select one area below to manage the coffee shop without loading every panel at once.</p>

        <div class="mt-8 grid md:grid-cols-3 gap-5">
            <a href="admin.php?section=menu" class="rounded-3xl border border-stone-200 bg-stone-50 p-6 hover:border-amber-400 hover:bg-amber-50 transition">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Menu</p>
                <h2 class="mt-3 text-3xl font-display">Manage items</h2>
                <p class="mt-3 text-stone-600">Create, update, and delete coffee shop menu items.</p>
            </a>
            <a href="admin.php?section=users" class="rounded-3xl border border-stone-200 bg-stone-50 p-6 hover:border-amber-400 hover:bg-amber-50 transition">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Users</p>
                <h2 class="mt-3 text-3xl font-display">Control accounts</h2>
                <p class="mt-3 text-stone-600">Lock and unlock user accounts from one focused screen.</p>
            </a>
            <a href="admin.php?section=orders" class="rounded-3xl border border-stone-200 bg-stone-50 p-6 hover:border-amber-400 hover:bg-amber-50 transition">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-500">Orders</p>
                <h2 class="mt-3 text-3xl font-display">Update status</h2>
                <p class="mt-3 text-stone-600">Review orders and save their current transaction status.</p>
            </a>
        </div>
    </section>
<?php endif; ?>

<?php if ($section === 'menu'): ?>
    <section class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-700">Admin Dashboard</p>
                <h1 class="mt-2 text-4xl font-display">Menu management</h1>
            </div>
            <a href="admin.php" class="rounded-xl border border-stone-300 px-4 py-2 font-medium">Back to dashboard</a>
        </div>

        <div class="grid xl:grid-cols-[0.9fr_1.1fr] gap-8">
            <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
                <h2 class="text-3xl font-display"><?= $editMenu ? 'Update menu item' : 'Create menu item' ?></h2>
                <p class="mt-3 text-stone-600">Add new menu items or update an existing one from this section.</p>

                <?php if ($error): ?>
                    <div class="mt-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3"><?= h($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="mt-6 space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $editMenu ? 'update_menu' : 'create_menu' ?>">
                    <input type="hidden" name="menu_id" value="<?= (int) ($editMenu['id'] ?? 0) ?>">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input type="text" name="name" value="<?= h($editMenu['name'] ?? '') ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border border-stone-300 px-4 py-3" required><?= h($editMenu['description'] ?? '') ?></textarea>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Price</label>
                            <input type="number" name="price" min="0" step="0.01" value="<?= h((string) ($editMenu['price'] ?? '0.00')) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Stock</label>
                            <input type="number" name="stock" min="0" value="<?= h((string) ($editMenu['stock'] ?? 0)) ?>" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Category</label>
                            <select name="category_id" class="w-full rounded-xl border border-stone-300 px-4 py-3" required>
                                <option value="">Select</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= ((int) ($editMenu['category_id'] ?? 0) === (int) $category['id']) ? 'selected' : '' ?>>
                                        <?= h($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="rounded-xl bg-amber-600 text-white px-5 py-3 font-semibold">
                            <?= $editMenu ? 'Update menu item' : 'Create menu item' ?>
                        </button>
                        <?php if ($editMenu): ?>
                            <a href="admin.php?section=menu" class="rounded-xl border border-stone-300 px-5 py-3 font-semibold">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
                <h2 class="text-3xl font-display">Manage items</h2>
                <div class="mt-6 space-y-4">
                    <?php foreach ($menuItems as $item): ?>
                        <div class="rounded-2xl border border-stone-200 p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div>
                                    <div class="text-xl font-semibold"><?= h($item['name']) ?></div>
                                    <div class="text-sm text-stone-500 mt-1"><?= h($item['category_name']) ?> | PHP <?= number_format((float) $item['price'], 2) ?> | Stock <?= (int) $item['stock'] ?></div>
                                    <p class="mt-3 text-stone-600"><?= h($item['description']) ?></p>
                                </div>
                                <div class="flex gap-3">
                                    <a href="admin.php?section=menu&edit_menu=<?= (int) $item['id'] ?>" class="rounded-xl border border-stone-300 px-4 py-2 text-sm font-medium">Edit</a>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_menu">
                                        <input type="hidden" name="menu_id" value="<?= (int) $item['id'] ?>">
                                        <button type="submit" class="rounded-xl bg-red-600 text-white px-4 py-2 text-sm font-medium">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($section === 'users'): ?>
    <section class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-700">Admin Dashboard</p>
                <h1 class="mt-2 text-4xl font-display">User account controls</h1>
            </div>
            <a href="admin.php" class="rounded-xl border border-stone-300 px-4 py-2 font-medium">Back to dashboard</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
            <h2 class="text-3xl font-display">Authentication status</h2>
            <div class="mt-6 space-y-4">
                <?php foreach ($users as $user): ?>
                    <div class="rounded-2xl border border-stone-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-semibold text-lg"><?= h($user['full_name']) ?></div>
                                <div class="text-sm text-stone-500"><?= h($user['email']) ?></div>
                                <div class="text-xs uppercase tracking-[0.2em] text-stone-400 mt-2"><?= h($user['role']) ?></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm <?= $user['lockout_until'] ? 'text-red-700' : 'text-emerald-700' ?>">
                                    <?= $user['lockout_until'] ? 'Locked' : 'Active' ?>
                                </div>
                                <?php if ((int) $user['id'] !== current_user_id()): ?>
                                    <form method="POST" class="mt-3">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="target_user_id" value="<?= (int) $user['id'] ?>">
                                        <?php if ($user['lockout_until']): ?>
                                            <input type="hidden" name="action" value="unlock_user">
                                            <button type="submit" class="rounded-xl bg-stone-900 text-white px-4 py-2 text-sm font-medium">Unlock</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="lock_user">
                                            <button type="submit" class="rounded-xl bg-amber-600 text-white px-4 py-2 text-sm font-medium">Lock</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($section === 'orders'): ?>
    <section class="space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-amber-700">Admin Dashboard</p>
                <h1 class="mt-2 text-4xl font-display">Order status controls</h1>
            </div>
            <a href="admin.php" class="rounded-xl border border-stone-300 px-4 py-2 font-medium">Back to dashboard</a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-stone-200 p-8">
            <h2 class="text-3xl font-display">Update transaction status</h2>
            <div class="mt-6 space-y-4">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $isCompleted = $order['status'] === 'completed';
                    $isCancelled = $order['status'] === 'cancelled';
                    $cardClass = $isCompleted
                        ? 'border-emerald-200 bg-emerald-50'
                        : ($isCancelled ? 'border-stone-300 bg-stone-100' : 'border-red-200 bg-red-50');
                    $buttonClass = $isCompleted
                        ? 'bg-emerald-700'
                        : ($isCancelled ? 'bg-stone-700' : 'bg-red-700');
                    ?>
                    <div class="rounded-2xl border p-5 <?= $cardClass ?>">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div>
                                <div class="font-semibold text-lg">Order #<?= (int) $order['id'] ?></div>
                                <div class="text-sm text-stone-500"><?= h($order['full_name']) ?> | PHP <?= number_format((float) $order['total_amount'], 2) ?></div>
                                <div class="text-sm text-stone-500 mt-1">Created <?= h($order['created_at']) ?></div>
                            </div>
                            <form method="POST" class="flex gap-3">
                                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="update_order_status">
                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                <select name="status" class="rounded-xl border border-stone-300 bg-white px-4 py-3">
                                    <?php foreach (['pending', 'completed', 'cancelled'] as $status): ?>
                                        <option value="<?= h($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= h(ucfirst($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="rounded-xl <?= $buttonClass ?> text-white px-4 py-3 font-medium">Save</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php layout_footer(); ?>
