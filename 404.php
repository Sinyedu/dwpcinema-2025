<?php
http_response_code(404);
header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 404 Not Found');
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>404 Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 text-gray-800 flex items-center justify-center">
    <main class="w-full max-w-3xl p-8">
        <div class="rounded-2xl bg-white shadow-lg ring-1 ring-gray-100 overflow-hidden">
            <div class="p-8 sm:p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-50 text-red-600 mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0L21 15.344M3 3l18 18" />
                    </svg>
                </div>

                <h1 class="text-6xl font-extrabold tracking-tight mb-2">404</h1>
                <p class="text-lg text-gray-600 mb-6">Oops — we can't find the page you're looking for.</p>
                <p class="text-lg text-gray-600 mb-6">Or the page you are trying to find is being built!</p>

                <div class="flex flex-col sm:flex-row gap-3 items-center justify-center">
                    <a href="<?php echo htmlspecialchars($baseUrl ?: '/'); ?>" class="inline-flex items-center px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Go to homepage
                    </a>

                    <button onclick="history.back()" class="inline-flex items-center px-5 py-3 border border-gray-200 bg-white rounded-md hover:bg-gray-50">
                        Go back
                    </button>
                </div>

                <p class="mt-6 text-sm text-gray-400">If you believe this is an error, contact the site administrator.</p>
            </div>

            <footer class="border-t border-gray-100 px-6 py-4 text-xs text-gray-400 text-center">
                &copy; <?php echo date('Y'); ?> — <?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'Your site'); ?>
            </footer>
        </div>
    </main>
</body>

</html>