<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../controllers/LocationController.php';

$pdo = Database::getInstance();
$locationController = new LocationController($pdo);

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $locationController->addLocation([
                'locationName' => $_POST['locationName'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'postcode' => $_POST['postcode'],
                'country' => $_POST['country']
            ]);
            $success = "Location added successfully!";
        }

        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            $locationController->updateLocation($_POST['locationID'], [
                'locationName' => $_POST['locationName'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'postcode' => $_POST['postcode'],
                'country' => $_POST['country']
            ]);
            $success = "Location updated successfully!";
        }

        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $locationController->deleteLocation($_POST['locationID']);
            $success = "Location deleted successfully!";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$locations = $locationController->getAllLocations();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Locations - DWP Esports Cinema</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-900 min-h-screen flex">
    <?php include "../includes/adminSidebar.php"; ?>

    <div class="flex-1 ml-64 p-8">
        <header class="flex justify-between items-center mb-8 border-b pb-4">
            <h1 class="text-2xl text-white font-semibold">Manage Locations</h1>
            <a href="dashboard.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-500 text-sm">Back to Dashboard</a>
        </header>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-neutral-800 p-6 rounded-lg shadow mb-6">
            <h2 class="text-white text-lg font-semibold mb-4">Add New Location</h2>
            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="action" value="add">
                <input type="text" name="locationName" placeholder="Location Name" required
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                <input type="text" name="address" placeholder="Address" required
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                <input type="text" name="city" placeholder="City" required
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                <input type="text" name="postcode" placeholder="Postcode"
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                <input type="text" name="country" placeholder="Country" value="Denmark"
                    class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                <div class="md:col-span-2 text-center mt-2">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-500">Add Location</button>
                </div>
            </form>
        </div>

        <!-- Locations Table -->
        <div class="bg-neutral-800 p-6 rounded-lg shadow">
            <h2 class="text-white text-lg font-semibold mb-4">All Locations</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-gray-300">
                    <thead class="bg-neutral-700 text-gray-200 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Address</th>
                            <th class="px-4 py-2">City</th>
                            <th class="px-4 py-2">Postcode</th>
                            <th class="px-4 py-2">Country</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locations as $loc): ?>
                            <tr class="border-b border-neutral-700">
                                <form method="POST" class="flex flex-wrap">
                                    <input type="hidden" name="locationID" value="<?= $loc['locationID'] ?>">
                                    <td class="px-2 py-2"><?= $loc['locationID'] ?></td>
                                    <td class="px-2 py-2">
                                        <input type="text" name="locationName" value="<?= htmlspecialchars($loc['locationName']) ?>"
                                            class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" name="address" value="<?= htmlspecialchars($loc['address']) ?>"
                                            class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" name="city" value="<?= htmlspecialchars($loc['city']) ?>"
                                            class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" name="postcode" value="<?= htmlspecialchars($loc['postcode']) ?>"
                                            class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" name="country" value="<?= htmlspecialchars($loc['country']) ?>"
                                            class="px-2 py-1 rounded bg-neutral-700 text-white border border-gray-600">
                                    </td>
                                    <td class="px-2 py-2 flex gap-2">
                                        <button type="submit" name="action" value="update"
                                            class="px-2 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-500">Update
                                        </button>
                                        <button type="submit" name="action" value="delete"
                                            class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-500"
                                            onclick="return confirm('Are you sure you want to delete this location?')">Delete
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($locations)): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-400">No locations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>