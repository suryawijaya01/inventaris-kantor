<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Check if category is being used
    $checkQuery = "SELECT COUNT(*) as total FROM inventaris WHERE kategori_id = $id";
    $checkResult = $db->query($checkQuery);
    $checkData = $checkResult->fetch_assoc();
    
    if ($checkData['total'] > 0) {
        header('Location: index.php?error=' . urlencode('Tidak dapat menghapus kategori yang masih digunakan oleh inventaris'));
        exit();
    }
    
    // Delete category
    $deleteQuery = "DELETE FROM kategori WHERE id = $id";
    if ($db->query($deleteQuery)) {
        header('Location: index.php?success=' . urlencode('Kategori berhasil dihapus'));
    } else {
        header('Location: index.php?error=' . urlencode('Gagal menghapus kategori'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID tidak valid'));
}

exit();
?>