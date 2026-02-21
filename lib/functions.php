<?php
require_once __DIR__ . '/../config/database.php';

class Functions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Get base path from .env
    public function getBasePath() {
        return $_ENV['BASE_PATH'];
    }
    
    // Format tanggal Indonesia
    public function formatTanggal($tanggal) {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $split = explode('-', $tanggal);
        return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
    }
    
    // Generate kode inventaris
    public function generateKodeInventaris() {
        $query = "SELECT kode_inventaris FROM inventaris ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = $row['kode_inventaris'];
            $number = (int)substr($lastCode, 4) + 1;
        } else {
            $number = 1;
        }
        
        return 'INV-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
    
    // Generate nomor peminjaman
    public function generateNomorPeminjaman() {
        $query = "SELECT nomor_peminjaman FROM peminjaman ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = $row['nomor_peminjaman'];
            $number = (int)substr($lastCode, 4) + 1;
        } else {
            $number = 1;
        }
        
        return 'PJM-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
    
    // Get all categories
    public function getKategori() {
        $query = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
        $result = $this->db->query($query);
        $categories = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        return $categories;
    }
    
    // Get category name by ID
    public function getKategoriName($id) {
        $id = $this->db->real_escape_string($id);
        $query = "SELECT nama_kategori FROM kategori WHERE id = $id";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['nama_kategori'];
        }
        
        return '-';
    }
    
    // Get username by user ID
    public function getUsername($user_id) {
        $user_id = $this->db->real_escape_string($user_id);
        $query = "SELECT username FROM users WHERE id = $user_id";
        $result = $this->db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['username'];
        }
        
        return '-';
    }
    
    // Upload foto
    public function uploadFoto($file, $oldFile = null) {
        $targetDir = __DIR__ . "/../uploads/";
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Format file tidak diizinkan'];
        }
        
        if ($file["size"] > 5000000) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar'];
        }
        
        $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $targetFile = $targetDir . $newFileName;
        
        if (move_uploaded_file($file["tmp_name"], $targetFile)) {
            // Delete old file if exists
            if ($oldFile && file_exists($targetDir . $oldFile)) {
                unlink($targetDir . $oldFile);
            }
            
            return ['success' => true, 'filename' => $newFileName];
        }
        
        return ['success' => false, 'message' => 'Gagal upload file'];
    }
    
    // Delete foto
    public function deleteFoto($filename) {
        $targetDir = __DIR__ . "/../uploads/";
        $filePath = $targetDir . $filename;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    // Get alert HTML
    public function getAlert($type, $message) {
        $icons = [
            'success' => 'fa-check-circle',
            'danger' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        
        $icon = isset($icons[$type]) ? $icons[$type] : $icons['info'];
        
        return '
        <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
            <i class="fas ' . $icon . ' me-2"></i>
            ' . $message . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    
    // Check if date is late
    public function isLate($tanggal_kembali_rencana, $tanggal_kembali_aktual = null) {
        $rencana = strtotime($tanggal_kembali_rencana);
        $aktual = $tanggal_kembali_aktual ? strtotime($tanggal_kembali_aktual) : time();
        
        return $aktual > $rencana;
    }
    
    // Get status badge
    public function getStatusBadge($status) {
        $badges = [
            'diajukan' => '<span class="badge bg-warning">Diajukan</span>',
            'disetujui' => '<span class="badge bg-success">Disetujui</span>',
            'dikembalikan' => '<span class="badge bg-info">Dikembalikan</span>',
            'terlambat' => '<span class="badge bg-danger">Terlambat</span>',
            'dibatalkan' => '<span class="badge bg-secondary">Dibatalkan</span>'
        ];
        
        return isset($badges[$status]) ? $badges[$status] : $status;
    }
}
?>