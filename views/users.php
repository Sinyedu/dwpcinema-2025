<?php
session_start();
include __DIR__ . '/../includes/adminSidebar.php'; 


if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
