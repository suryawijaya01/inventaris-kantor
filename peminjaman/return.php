<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $peminjaman_id = isset($_POST['peminjaman_id']) ? (int)$_POST['peminjaman_id'] : 0;
    $tanggal_kembali_aktual = $db->real_escape_string($_POST['tanggal_kembali_aktual']);
    $kondisi_barang = $db->real_escape_string($_POST['kondisi_barang']);
    $catatan_pengembalian = $db->real_escape_string(trim($_POST['catatan_pengembalian']));
    
    if ($peminjaman_id > 0 && !empty($tanggal_kembali_aktual)) {
        // Get peminjaman data
        $query = "SELECT * FROM peminjaman WHERE id = $peminjaman_id AND status = 'disetujui'";
        $result = $db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            // Determine status based on return date
            $status = 'dikembalikan';
            if (strtotime($tanggal_kembali_aktual) > strtotime($data['tanggal_kembali_rencana'])) {
                $status = 'terlambat';
            }
            
            // Build catatan with existing note and return note
            $fullCatatan = $data['catatan'];
            if (!empty($kondisi_barang) || !empty($catatan_pengembalian)) {
                $returnNote = "\n\n--- CATATAN PENGEMBALIAN ---\n";
                $returnNote .= "Tanggal Pengembalian: " . date('d/m/Y', strtotime($tanggal_kembali_aktual)) . "\n";
                $returnNote .= "Kondisi Barang: " . $kondisi_barang . "\n";
                if (!empty($catatan_pengembalian)) {
                    $returnNote .= "Catatan: " . $catatan_pengembalian;
                }
                $fullCatatan .= $returnNote;
            }
            
            // Return stock
            $detailQuery = "SELECT * FROM peminjaman_detail WHERE peminjaman_id = $peminjaman_id";
            $detailResult = $db->query($detailQuery);
            
            while ($detail = $detailResult->fetch_assoc()) {
                $returnQuery = "UPDATE inventaris SET jumlah_tersedia = jumlah_tersedia + {$detail['jumlah']} 
                                WHERE id = {$detail['inventaris_id']}";
                $db->query($returnQuery);
            }
            
            // Update peminjaman
            $updateQuery = "UPDATE peminjaman SET 
                            status = '$status', 
                            tanggal_kembali_aktual = '$tanggal_kembali_aktual',
                            catatan = '$fullCatatan'
                            WHERE id = $peminjaman_id";
            
            if ($db->query($updateQuery)) {
                $message = $status == 'terlambat' ? 
                    'Peminjaman berhasil dikembalikan (TERLAMBAT)' : 
                    'Peminjaman berhasil dikembalikan';
                header('Location: index.php?success=' . urlencode($message));
            } else {
                header('Location: index.php?error=' . urlencode('Gagal memproses pengembalian'));
            }
        } else {
            header('Location: index.php?error=' . urlencode('Data peminjaman tidak ditemukan atau status tidak valid'));
        }
    } else {
        header('Location: index.php?error=' . urlencode('Data tidak lengkap'));
    }
} else {
    header('Location: index.php?error=' . urlencode('Method tidak valid'));
}

exit();
?>