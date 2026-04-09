<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

function app_log(string $type, string $message, array $context = []): void
{
    $formattedContext = $context ? json_encode($context, JSON_UNESCAPED_SLASHES) : '';
    $line = sprintf(
        "[%s] %-14s %s %s%s",
        date('Y-m-d H:i:s'),
        strtoupper($type),
        $message,
        $formattedContext,
        PHP_EOL
    );
    
    // Log to strictly local file
    error_log($line, 3, LOG_FILE);
    
    // Log directly to the OS syslog
    openlog('ITSECWB_GreenBean', LOG_PID | LOG_PERROR, LOG_USER);
    $syslogPriority = match (strtolower($type)) {
        'error' => LOG_ERR,
        'auth' => LOG_WARNING,
        'admin' => LOG_INFO,
        default => LOG_INFO,
    };
    $syslogMessage = sprintf("[%s] %s %s", strtoupper($type), $message, $formattedContext);
    syslog($syslogPriority, $syslogMessage);
    closelog();
}

function render_error_page(Throwable $exception): void
{
    http_response_code(500);
    $message = APP_DEBUG ? $exception->getMessage() : 'Something went wrong. Please try again later.';
    $details = APP_DEBUG ? nl2br(htmlspecialchars((string) $exception)) : '';

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Error</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head><body class="bg-stone-100 min-h-screen flex items-center justify-center p-6">';
    echo '<div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl p-8">';
    echo '<h1 class="text-2xl font-bold text-red-700 mb-4">Application Error</h1>';
    echo '<p class="text-stone-700 mb-4">' . htmlspecialchars($message) . '</p>';
    if ($details !== '') {
        echo '<div class="bg-stone-900 text-stone-100 rounded-xl p-4 text-sm overflow-x-auto">' . $details . '</div>';
    }
    echo '</div></body></html>';
    exit;
}

set_exception_handler(function (Throwable $exception): void {
    app_log('error', 'Unhandled exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
    ]);
    render_error_page($exception);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if (FORCE_HTTPS) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    if (!$isHttps) {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: https://' . $host . $uri);
        exit;
    }
}

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => FORCE_HTTPS,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ensure_csrf_token(): void
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

ensure_csrf_token();

if (isset($_SESSION['user_id'], $_SESSION['last_activity']) && (time() - (int) $_SESSION['last_activity']) > SESSION_TIMEOUT_SECONDS) {
    app_log('auth', 'Session timed out', ['user_id' => $_SESSION['user_id']]);
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash_error'] = 'Your session expired due to inactivity. Please sign in again.';
    ensure_csrf_token();
}

$_SESSION['last_activity'] = time();

require_once __DIR__ . '/db_connect.php';

function csrf_token(): string
{
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        throw new RuntimeException('Invalid CSRF token.');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

function take_flash(string $type): ?string
{
    $key = 'flash_' . $type;
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);
    return $message;
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        throw new RuntimeException('Access denied.');
    }
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function uploaded_photo_data(?array $file): ?string
{
    if (!$file || empty($file['tmp_name'])) {
        return null;
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Profile photo must be 2MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
        throw new RuntimeException('Invalid file type. Only JPG and PNG are allowed.');
    }

    return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($file['tmp_name']));
}

function layout_header(string $title): void
{
    $success = take_flash('success');
    $error = take_flash('error');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . h($title) . ' - ' . h(APP_NAME) . '</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">';
    echo '<style>body{font-family:Inter,sans-serif}.font-display{font-family:"Playfair Display",serif}</style>';
    echo '</head><body class="bg-stone-50 text-stone-900">';
    echo '<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-stone-200"><div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">';
    echo '<a href="index.php" class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-emerald-800 text-white flex items-center justify-center font-bold">G</div><div><div class="font-display text-xl leading-none">The Green Bean</div></div></a>';
    echo '<div class="flex items-center gap-4 text-sm font-medium">';
    echo '<a href="index.php" class="hover:text-emerald-800">Home</a>';
    if (is_logged_in()) {
        echo '<a href="reviews.php" class="hover:text-emerald-800">My Reviews</a>';
        echo '<a href="profile.php" class="hover:text-emerald-800">Profile</a>';
        if (is_admin()) {
            echo '<a href="admin.php" class="hover:text-emerald-800">Admin</a>';
        }
        echo '<a href="logout.php" class="hover:text-emerald-800">Logout</a>';
    } else {
        echo '<a href="login.php" class="hover:text-emerald-800">Login</a>';
        echo '<a href="register.php" class="px-4 py-2 rounded-full bg-emerald-800 text-white">Register</a>';
    }
    echo '</div></div></nav><main class="max-w-6xl mx-auto px-4 py-10">';
    if ($success) {
        echo '<div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3">' . h($success) . '</div>';
    }
    if ($error) {
        echo '<div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3">' . h($error) . '</div>';
    }
}

function layout_footer(): void
{
    echo '</main></body></html>';
}
