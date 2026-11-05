<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$aboutModel = new AboutUs(db());
if (is_post()) {
    require_csrf();
    $data = [
        'aboutTitle' => post_str('aboutTitle'),
        'aboutContent' => post_str('aboutContent'),
        'aboutFooter' => post_str('aboutFooter'),
    ];
    $id = (int)($_POST['aboutID'] ?? 0);

    if (isset($_POST['delete'])) {
        $aboutModel->delete($id);
    } elseif ($data['aboutTitle'] === '' || $data['aboutContent'] === '') {
        flash('Title and content are required.');
    } elseif (isset($_POST['add'])) {
        $aboutModel->create($data);
    } elseif (isset($_POST['update'])) {
        $aboutModel->update($id, $data);
    }
    redirect('views/aboutus.php');
}

$aboutItems = $aboutModel->getAll();
$editItem = isset($_GET['edit']) ? $aboutModel->getById((int)$_GET['edit']) : null;
$csrfToken = $csrf->token();
?>
<?php
$pageTitle = 'Admin About Us - DWP';
include __DIR__ . '/../includes/head.php';
?>

<body class="bg-neutral-900 min-h-screen">
    <?php include __DIR__ . '/../includes/adminSidebar.php'; ?>
    <div class="max-w-6xl mx-auto px-6 py-10">

        <h1 class="text-2xl text-white font-semibold mb-6">Manage About Us</h1>

        <?php if ($editItem): ?>
            <h2 class="text-xl text-white font-semibold mb-2">Edit Section</h2>
            <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-6">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="aboutID" value="<?= $editItem['aboutID'] ?>">
                <input type="text" name="aboutTitle" value="<?= htmlspecialchars($editItem['aboutTitle']) ?>" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white" placeholder="Title" required>
                <textarea name="aboutContent" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white" placeholder="Content" required><?= htmlspecialchars($editItem['aboutContent']) ?></textarea>
                <input type="text" name="aboutFooter" value="<?= htmlspecialchars($editItem['aboutFooter']) ?>" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white" placeholder="Footer (optional)">
                <div class="flex space-x-2">
                    <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-400">Update</button>
                    <a href="aboutus.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-400">Cancel</a>
                </div>
            </form>
        <?php endif; ?>

        <h2 class="text-xl text-white font-semibold mb-2">Add Section</h2>
        <form method="POST" class="space-y-4 bg-neutral-800 p-6 rounded shadow mb-10">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="text" name="aboutTitle" placeholder="Title" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white" required>
            <textarea name="aboutContent" placeholder="Content" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white" required></textarea>
            <input type="text" name="aboutFooter" placeholder="Footer (optional)" class="w-full border rounded px-3 py-2 bg-neutral-700 text-white">
            <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500">Add Section</button>
        </form>

        <table class="w-full table-auto bg-neutral-800 rounded shadow overflow-hidden">
            <thead>
                <tr class="bg-gray-700 text-white">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Content</th>
                    <th class="px-4 py-2">Footer</th>
                    <th class="px-4 py-2">Last Updated</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($aboutItems as $item): ?>
                    <tr class="border-b border-gray-700">
                        <td class="px-4 py-2 text-white"><?= $item['aboutID'] ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($item['aboutTitle']) ?></td>
                        <td class="px-4 py-2 text-white"><?= nl2br(htmlspecialchars($item['aboutContent'])) ?></td>
                        <td class="px-4 py-2 text-white"><?= htmlspecialchars($item['aboutFooter']) ?></td>
                        <td class="px-4 py-2 text-white"><?= $item['lastUpdated'] ?></td>
                        <td class="px-4 py-2 space-x-2">
                            <a href="aboutus.php?edit=<?= $item['aboutID'] ?>" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-400">Edit</a>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                <input type="hidden" name="aboutID" value="<?= $item['aboutID'] ?>">
                                <button type="submit" name="delete" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-500">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>

</html>