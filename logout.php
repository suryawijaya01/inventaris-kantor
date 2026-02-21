<?php
require_once __DIR__ . '/lib/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: ' . $_ENV['BASE_PATH'] . '/login.php');
exit();
?>