<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireLogin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Get current data
    $query = "SELECT * FROM peminjaman WHERE id = $id";
    $result = $db->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Check if user is authorized (admin or owner with status 'diajukan')
        if (!$auth->isAdmin() && ($data['user_id'] != $_SESSION['user_id'] || $data['status'] != 'diajukan')) {
            header('Location: index.php?error=' . urlencode('Tidak memiliki izin untuk menghapus peminjaman ini'));
            exit();
        }
        
        // Only allow delete if status is 'diajukan'
        if ($data['status'] != 'diajukan') {
            header('Location: index.php?error=' . urlencode('Hanya peminjaman dengan status diajukan yang dapat dihapus'));
            exit();
        }
        
        // Return stock
        $detailQuery = "SELECT * FROM peminjaman_detail WHERE peminjaman_id = $id";
        $detailResult = $db->query($detailQuery);
        
        while ($detail = $detailResult->fetch_assoc()) {
            $returnQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia + {$detail['jumlah']} 
                            WHERE id = {$detail['inventaris_id']}";
            $db->query($returnQuery);
        }
        
        // Update status to dibatalkan instead of deleting
        $updateQuery = "UPDATE peminjaman SET status = 'dibatalkan' WHERE id = $id";
        if ($db->query($updateQuery)) {
            header('Location: index.php?success=' . urlencode('Peminjaman berhasil dibatalkan'));
        } else {
            header('Location: index.php?error=' . urlencode('Gagal membatalkan peminjaman'));
        }
    } else {
        header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID tidak valid'));
}

exit();
?>