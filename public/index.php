<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=dwpcinemaDB;charset=utf8", "root", "");

// Get featured tournaments
$stmt = $pdo->query("
    SELECT t.tournamentID, t.tournamentName, t.tournamentDescription, t.startDate, g.gameName
    FROM Tournament t
    JOIN Game g ON t.gameID = g.gameID
    ORDER BY t.startDate ASC
    LIMIT 3
");
$featured = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get latest news
$stmt2 = $pdo->query("
    SELECT newsID, newsTitle, newsContent, newsAuthor, newsImage, newsCreatedAt
    FROM News
    ORDER BY newsCreatedAt DESC
    LIMIT 3
");
$news = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DWP Esports Cinema</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-900">

  <!-- Header -->
  <header class="border-b border-gray-200 bg-gray-50">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">DWP Esports Cinema</h1>
      <nav class="flex gap-6 text-sm">
        <?php if(isset($_SESSION['user_id'])): ?>
          <span class="text-gray-600">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
          <a href="users.php" class="hover:text-gray-900">Community</a>
          <a href="logout.php" class="hover:text-gray-900">Logout</a>
        <?php else: ?>
          <a href="admin_login.php" class="hover:text-gray-900">Admin Login</a>
          <a href="login.php" class="hover:text-gray-900">Login</a>
          <a href="register.php" class="hover:text-gray-900">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="relative bg-gray-900 text-white">
    <img src="https://via.placeholder.com/1600x600" alt="Hero" class="w-full h-[400px] object-cover opacity-70">
    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-6">
      <h2 class="text-4xl font-bold">Esports on the Big Screen</h2>
      <p class="mt-4 text-lg text-gray-200 max-w-2xl">
        Book your seat for the biggest esports tournaments and watch with the community.
      </p>
      <a href="register.php" class="mt-6 inline-block px-6 py-3 rounded-md font-medium bg-blue-600 hover:bg-blue-500 transition">
        Get Started
      </a>
    </div>
  </section>

  <!-- Featured Tournaments -->
  <section class="max-w-6xl mx-auto px-6 py-16">
    <h3 class="text-2xl font-semibold text-center">Featured Tournaments</h3>
    <div class="grid md:grid-cols-3 gap-8 mt-8">
      <?php foreach($featured as $t): ?>
        <div class="bg-gray-100 rounded-lg overflow-hidden shadow">
          <img src="https://via.placeholder.com/600x400" alt="<?= htmlspecialchars($t['tournamentName']) ?>" class="w-full h-48 object-cover">
          <div class="p-4">
            <h4 class="font-semibold text-lg"><?= htmlspecialchars($t['tournamentName']) ?></h4>
            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($t['gameName']) ?> — <?= htmlspecialchars($t['startDate']) ?></p>
            <p class="text-sm text-gray-600 mt-2"><?= htmlspecialchars($t['tournamentDescription']) ?></p>
            <a href="#" class="mt-4 inline-block text-blue-600 font-medium hover:underline">Book Tickets</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- News Section -->
  <section class="bg-gray-50 border-t border-gray-200 py-16">
    <div class="max-w-6xl mx-auto px-6">
      <h3 class="text-2xl font-semibold text-center">Latest News</h3>
      <div class="grid md:grid-cols-3 gap-8 mt-8">
        <?php foreach($news as $n): ?>
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <?php if($n['newsImage']): ?>
              <img src="<?= htmlspecialchars($n['newsImage']) ?>" alt="<?= htmlspecialchars($n['newsTitle']) ?>" class="w-full h-40 object-cover">
            <?php endif; ?>
            <div class="p-6">
              <h4 class="font-semibold text-lg"><?= htmlspecialchars($n['newsTitle']) ?></h4>
              <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars(substr($n['newsContent'], 0, 120)) ?>...</p>
              <p class="mt-2 text-xs text-gray-400">By <?= htmlspecialchars($n['newsAuthor']) ?> | <?= htmlspecialchars($n['newsCreatedAt']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</body>
</html>
