<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0 && !empty($action)) {
    // Get current data
    $query = "SELECT * FROM peminjaman WHERE id = $id";
    $result = $db->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        switch ($action) {
            case 'approve':
                // Approve peminjaman
                if ($data['status'] == 'diajukan') {
                    $updateQuery = "UPDATE peminjaman SET status = 'disetujui' WHERE id = $id";
                    if ($db->query($updateQuery)) {
                        header('Location: index.php?success=' . urlencode('Peminjaman berhasil disetujui'));
                    } else {
                        header('Location: index.php?error=' . urlencode('Gagal menyetujui peminjaman'));
                    }
                } else {
                    header('Location: index.php?error=' . urlencode('Status peminjaman tidak valid untuk disetujui'));
                }
                break;
                
            case 'reject':
                // Reject peminjaman - return stock
                if ($data['status'] == 'diajukan') {
                    $detailQuery = "SELECT * FROM peminjaman_detail WHERE peminjaman_id = $id";
                    $detailResult = $db->query($detailQuery);
                    
                    while ($detail = $detailResult->fetch_assoc()) {
                        $returnQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia + {$detail['jumlah']} 
                                        WHERE id = {$detail['inventaris_id']}";
                        $db->query($returnQuery);
                    }
                    
                    $updateQuery = "UPDATE peminjaman SET status = 'dibatalkan' WHERE id = $id";
                    if ($db->query($updateQuery)) {
                        header('Location: index.php?success=' . urlencode('Peminjaman berhasil ditolak'));
                    } else {
                        header('Location: index.php?error=' . urlencode('Gagal menolak peminjaman'));
                    }
                } else {
                    header('Location: index.php?error=' . urlencode('Status peminjaman tidak valid untuk ditolak'));
                }
                break;
                
            default:
                header('Location: index.php?error=' . urlencode('Aksi tidak valid'));
                break;
        }
    } else {
        header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    }
} else {
    header('Location: index.php?error=' . urlencode('Parameter tidak valid'));
}

exit();
?>