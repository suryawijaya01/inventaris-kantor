<?php
$pageTitle = 'Dashboard';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();

// Get statistics with detailed queries
$statsQuery = [
    'total_karyawan' => "SELECT COUNT(*) as total FROM users",
    'total_admin' => "SELECT COUNT(*) as total FROM users WHERE role = 'admin'",
    'total_pegawai' => "SELECT COUNT(*) as total FROM users WHERE role = 'pegawai'",
    'total_inventaris' => "SELECT COUNT(*) as total FROM inventaris",
    'total_kategori' => "SELECT COUNT(*) as total FROM kategori",
    'total_peminjaman' => "SELECT COUNT(*) as total FROM peminjaman WHERE status != 'dibatalkan'",
    'peminjaman_aktif' => "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'disetujui'",
    'peminjaman_diajukan' => "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'diajukan'",
    'peminjaman_terlambat' => "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat'",
    'stok_menipis' => "SELECT COUNT(*) as total FROM inventaris WHERE jumlah_tersedia <= 5 AND jumlah_tersedia > 0",
    'stok_habis' => "SELECT COUNT(*) as total FROM inventaris WHERE jumlah_tersedia = 0"
];

$stats = [];
foreach ($statsQuery as $key => $query) {
    $result = $db->query($query);
    $stats[$key] = $result->fetch_assoc()['total'];
}
?>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topnav.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../views/breadcrumb.php'; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <!-- Karyawan Stats -->
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/karyawan/index.php" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_karyawan']; ?></h3>
                        <p>Total Karyawan</p>
                        <small style="color: #7f8c8d; font-size: 11px;">
                            Admin: <?php echo $stats['total_admin']; ?> | Pegawai: <?php echo $stats['total_pegawai']; ?>
                        </small>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/inventaris/index.php" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_inventaris']; ?></h3>
                        <p>Total Inventaris</p>
                        <small style="color: #7f8c8d; font-size: 11px;">
                            <?php echo $stats['total_kategori']; ?> Kategori
                        </small>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/peminjaman/index.php" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_peminjaman']; ?></h3>
                        <p>Total Peminjaman</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/peminjaman/index.php?status=diajukan" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['peminjaman_diajukan']; ?></h3>
                        <p>Menunggu Persetujuan</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/peminjaman/index.php?status=disetujui" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['peminjaman_aktif']; ?></h3>
                        <p>Peminjaman Aktif</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/peminjaman/index.php?status=terlambat" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['peminjaman_terlambat']; ?></h3>
                        <p>Terlambat</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/inventaris/index.php?stok=menipis" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['stok_menipis']; ?></h3>
                        <p>Stok Menipis</p>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="stat-card-wrapper">
            <a href="<?php echo $basePath; ?>/inventaris/index.php?stok=habis" class="stat-card-link">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['stok_habis']; ?></h3>
                        <p>Stok Habis</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <?php include __DIR__ . '/../views/admin_content.php'; ?>
    
    <?php include __DIR__ . '/../views/lower_block.php'; ?>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>