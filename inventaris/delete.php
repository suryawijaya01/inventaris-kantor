<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Get current data
    $query = "SELECT * FROM inventaris WHERE id = $id";
    $result = $db->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Check if item is being borrowed
        $checkQuery = "SELECT COUNT(*) as total FROM peminjaman_detail WHERE inventaris_id = $id";
        $checkResult = $db->query($checkQuery);
        $checkData = $checkResult->fetch_assoc();
        
        if ($checkData['total'] > 0) {
            header('Location: index.php?error=' . urlencode('Tidak dapat menghapus, inventaris masih terkait dengan peminjaman'));
            exit();
        }
        
        // Delete photo if exists
        if ($data['foto']) {
            $func->deleteFoto($data['foto']);
        }
        
        // Delete record
        $deleteQuery = "DELETE FROM inventaris WHERE id = $id";
        if ($db->query($deleteQuery)) {
            header('Location: index.php?success=' . urlencode('Data inventaris berhasil dihapus'));
        } else {
            header('Location: index.php?error=' . urlencode('Gagal menghapus data'));
        }
    } else {
        header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID tidak valid'));
}

exit();
?>