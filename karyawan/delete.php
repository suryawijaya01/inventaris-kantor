<?php
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/functions.php';

$auth = new Auth();
$auth->requireAdmin();

$func = new Functions();
$db = Database::getInstance()->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Prevent deleting own account
    if ($id == $_SESSION['user_id']) {
        header('Location: index.php?error=' . urlencode('Tidak dapat menghapus akun sendiri'));
        exit();
    }
    
    // Get user data
    $query = "SELECT * FROM users WHERE id = $id";
    $result = $db->query($query);
    
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Check if user has active borrowings
        $checkQuery = "SELECT COUNT(*) as total FROM peminjaman WHERE user_id = $id AND status IN ('diajukan', 'disetujui')";
        $checkResult = $db->query($checkQuery);
        $checkData = $checkResult->fetch_assoc();
        
        if ($checkData['total'] > 0) {
            header('Location: index.php?error=' . urlencode('Tidak dapat menghapus karyawan yang masih memiliki peminjaman aktif'));
            exit();
        }
        
        // Delete user (CASCADE will handle related records)
        $deleteQuery = "DELETE FROM users WHERE id = $id";
        if ($db->query($deleteQuery)) {
            header('Location: index.php?success=' . urlencode('Karyawan berhasil dihapus'));
        } else {
            header('Location: index.php?error=' . urlencode('Gagal menghapus karyawan'));
        }
    } else {
        header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    }
} else {
    header('Location: index.php?error=' . urlencode('ID tidak valid'));
}

exit();
?>