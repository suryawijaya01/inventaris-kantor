<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireLogin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$peminjaman_id = isset($_GET['peminjaman_id']) ? (int)$_GET['peminjaman_id'] : 0;

if ($id > 0 && $peminjaman_id > 0) {
    // Verify peminjaman belongs to user and status is 'diajukan'
    $checkQuery = "SELECT * FROM peminjaman WHERE id = $peminjaman_id AND user_id = {$_SESSION['user_id']} AND status = 'diajukan'";
    $checkResult = $db->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        header('Location: index.php?error=' . urlencode('Peminjaman tidak ditemukan atau tidak dapat diubah'));
        exit();
    }
    
    // Get detail data
    $detailQuery = "SELECT * FROM peminjaman_detail WHERE id = $id AND peminjaman_id = $peminjaman_id";
    $detailResult = $db->query($detailQuery);
    
    if ($detailResult && $detailResult->num_rows > 0) {
        $detail = $detailResult->fetch_assoc();
        
        // Check if this is the last item
        $countQuery = "SELECT COUNT(*) as total FROM peminjaman_detail WHERE peminjaman_id = $peminjaman_id";
        $countResult = $db->query($countQuery);
        $countData = $countResult->fetch_assoc();
        
        if ($countData['total'] <= 1) {
            header('Location: detail.php?id=' . $peminjaman_id . '&error=' . urlencode('Tidak dapat menghapus item terakhir. Batalkan peminjaman jika ingin menghapus semua item.'));
            exit();
        }
        
        // Return stock
        $returnQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia + {$detail['jumlah']} 
                        WHERE id = {$detail['inventaris_id']}";
        $db->query($returnQuery);
        
        // Delete detail
        $deleteQuery = "DELETE FROM peminjaman_detail WHERE id = $id";
        if ($db->query($deleteQuery)) {
            header('Location: detail.php?id=' . $peminjaman_id . '&success=' . urlencode('Item berhasil dihapus'));
        } else {
            header('Location: detail.php?id=' . $peminjaman_id . '&error=' . urlencode('Gagal menghapus item'));
        }
    } else {
        header('Location: detail.php?id=' . $peminjaman_id . '&error=' . urlencode('Item tidak ditemukan'));
    }
} else {
    header('Location: index.php?error=' . urlencode('Parameter tidak valid'));
}

exit();
?>