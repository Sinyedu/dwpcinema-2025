<footer class="bg-neutral-900 text-white mt-16">
    <div class="max-w-6xl mx-auto px-6 py-12 flex flex-col md:flex-row md:justify-evenly md:items-start gap-12">
        <div class="md:w-1/3">
            <h4 class="text-white font-semibold text-2xl mb-4">Opening Times</h4>
            <ul class="text-gray-300">
                <?php
                $stmt = $pdo->query("SELECT dayOfWeek, openTime, closeTime, isClosed FROM OpeningHours ORDER BY FIELD(dayOfWeek, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
                $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($hours as $h) {
                    echo '<li class="mb-1">';
                    echo htmlspecialchars($h['dayOfWeek']) . ': ';
                    if ($h['isClosed']) {
                        echo 'Closed';
                    } else {
                        echo date('H:i', strtotime($h['openTime'])) . ' - ' . date('H:i', strtotime($h['closeTime']));
                    }
                    echo '</li>';
                }
                ?>
            </ul>
        </div>

        <div class="md:w-1/3">
            <h4 class="text-white font-semibold text-lg mb-4">Quick Links</h4>
            <ul class="space-y-2 text-sm">
                <li><a href="index.php" class="hover:text-white transition">Home</a></li>
                <li><a href="tournaments.php" class="hover:text-white transition">Tournaments</a></li>
                <li><a href="showings.php" class="hover:text-white transition">Showings</a></li>
                <li><a href="news.php" class="hover:text-white transition">News</a></li>
                <li><a href="register.php" class="hover:text-white transition">Register</a></li>
                <li><a href="login.php" class="hover:text-white transition">Login</a></li>
            </ul>
        </div>

        <div class="md:w-1/3">
            <h4 class="text-white font-semibold text-lg mb-4">Connect with Us</h4>
            <p class="text-gray-400 text-sm mb-4">
                Email: <a href="mailto:reservations@simonnyblom.com" class="hover:text-white">reservations@simonnyblom.com</a>
            </p>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-white transition" aria-label="Facebook">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22 12c0-5.522-4.478-10-10-10S2 6.478 2 12c0 4.991 3.657 9.128 8.438 9.879v-6.987h-2.54v-2.892h2.54v-2.207c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.464h-1.26c-1.242 0-1.63.772-1.63 1.562v1.876h2.773l-.443 2.892h-2.33V21.88C18.343 21.128 22 16.991 22 12z" />
                    </svg>
                </a>
                <a href="#" class="hover:text-white transition" aria-label="Twitter">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.4 1a9.06 9.06 0 0 1-2.88 1.1 4.52 4.52 0 0 0-7.69 4.13 12.85 12.85 0 0 1-9.33-4.73 4.52 4.52 0 0 0 1.4 6.05A4.41 4.41 0 0 1 2 9.71v.05a4.52 4.52 0 0 0 3.63 4.44 4.52 4.52 0 0 1-2.04.08 4.52 4.52 0 0 0 4.22 3.14A9.06 9.06 0 0 1 1 19.54a12.79 12.79 0 0 0 6.92 2.03c8.3 0 12.84-6.88 12.84-12.85 0-.2 0-.42-.01-.63A9.22 9.22 0 0 0 23 3z" />
                    </svg>
                </a>
                <a href="#" class="hover:text-white transition" aria-label="Instagram">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zM12 7a5 5 0 1 1 0 10a5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7zm4.75-.88a1.12 1.12 0 1 1-2.24 0a1.12 1.12 0 0 1 2.24 0z" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="md:w-1/3">
            <h4 class="text-white font-semibold text-lg mb-4">Wanna meet us?</h4>
            <?php
            $locations = $locationController->getAllLocations();
            foreach ($locations as $loc): ?>
                <p class="text-gray-300 mb-1"><?= htmlspecialchars($loc['locationName']) ?></p>
                <p class="text-gray-400 text-sm"><?= htmlspecialchars($loc['address']) ?>, <?= htmlspecialchars($loc['city']) ?> <?= htmlspecialchars($loc['postcode']) ?></p>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="mt-8 pt-4 pb-2 text-center text-gray-400 text-xs">
        &copy; <?= date('Y') ?> DWP Esports Cinema. All rights reserved.
    </div>
</footer>