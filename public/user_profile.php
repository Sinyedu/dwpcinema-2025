<?php
session_start();
require_once "../database/connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM User WHERE userID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $avatar = $user['avatar'];

    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = "../uploads/avatars/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileTmp = $_FILES['avatar']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['avatar']['name']);
        $targetFile = $uploadDir . $fileName;

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($fileTmp, $targetFile)) {
                $avatar = "uploads/avatars/" . $fileName;
            }
        }
    }

    $stmt = $pdo->prepare("
        UPDATE User 
        SET firstName = ?, lastName = ?, userEmail = ?, avatar = ? 
        WHERE userID = ?
    ");
    $stmt->execute([$firstName, $lastName, $email, $avatar, $userID]);

    $_SESSION['user_name'] = $firstName . " " . $lastName;

    header("Location: user_profile.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile - DWP Esports Cinema</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900 min-h-screen">

<div class="max-w-3xl mx-auto px-6 py-12">
    <h1 class="text-2xl font-semibold mb-6 text-center">My Profile</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded mb-4 text-center">
            Profile updated successfully!
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">

        <div class="text-center">
            <img src="<?= htmlspecialchars($user['avatar'] ?? 'https://via.placeholder.com/120') ?>" 
                 alt="Avatar" class="w-28 h-28 rounded-full mx-auto mb-3 object-cover border">
            
            <label class="block text-sm text-gray-600 mb-1">Change Avatar</label>
            <input type="file" name="avatar" accept="image/*" 
                   class="w-full text-sm text-gray-700 border rounded px-3 py-2">
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" name="firstName" 
                       value="<?= htmlspecialchars($user['firstName']) ?>" 
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" name="lastName" 
                       value="<?= htmlspecialchars($user['lastName']) ?>" 
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" 
                   value="<?= htmlspecialchars($user['userEmail']) ?>" 
                   class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="text-right">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-500 text-white font-medium px-4 py-2 rounded">
                Save Changes
            </button>
        </div>
    </form>

    <div class="text-center mt-6">
        <a href="index.php" class="text-gray-600 hover:text-blue-600 text-sm">← Back to Home</a>
    </div>
</div>

</body>
</html>
