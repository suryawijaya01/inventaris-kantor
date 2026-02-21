<?php
require_once __DIR__ . '/lib/auth.php';

$auth = new Auth();

// Redirect to appropriate page based on login status
if ($auth->isLoggedIn()) {
    header('Location: ' . $_ENV['BASE_PATH'] . '/admin/index.php');
} else {
    header('Location: ' . $_ENV['BASE_PATH'] . '/login.php');
}
exit();
?>