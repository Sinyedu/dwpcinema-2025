<?php


try {
    $stmt = $pdo->query("SELECT newsID, newsTitle, newsDescription, createdAt FROM news ORDER BY createdAt DESC LIMIT 5");
    $newsItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $newsItems = [];
    echo "Error fetching news: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Star Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white font-sans">

<header class="fixed top-0 left-0 w-full bg-gray-900 bg-opacity-90 backdrop-blur-sm shadow z-50">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">
        <h1 class="text-yellow-400 font-bold text-xl">🎬 Star Cinema</h1>
        <nav class="space-x-6">
            <a href="#" class="hover:text-yellow-400">Home</a>
            <a href="#" class="hover:text-yellow-400">Now Showing</a>
            <a href="#" class="hover:text-yellow-400">Coming Soon</a>
            <a href="#" class="hover:text-yellow-400">Contact</a>
        </nav>
    </div>
</header>

<section class="relative h-screen flex items-center justify-center text-center mt-16">
    <img src="https://source.unsplash.com/1600x900/?cinema,movie-theater"
         alt="Cinema"
         class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black bg-opacity-70"></div>
    <div class="relative z-10 max-w-2xl px-4">
        <h2 class="text-4xl md:text-6xl font-bold mb-6">Experience Movies Like Never Before</h2>
        <button class="bg-yellow-400 text-black font-semibold px-6 py-3 rounded-full shadow-lg hover:scale-105 transition">
            Book Tickets
        </button>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-3xl font-bold text-yellow-400 text-center mb-10">🎥 Latest News</h2>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php foreach($newsItems as $news): ?>
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow hover:scale-105 transition transform duration-300">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-yellow-400 mb-2"><?=($news['newsTitle']) ?></h3>
                    <p class="text-gray-300 text-sm mb-2"><?=($news['newsDescription']) ?></p>
                    <p class="text-gray-500 text-xs"><?= date('F j, Y', strtotime($news['createdAt'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<footer class="bg-gray-800 py-6 mt-12 text-center text-gray-400 text-sm">
    &copy; <?= date("Y") ?> Star Cinema. All rights reserved.
</footer>

</body>
</html>
