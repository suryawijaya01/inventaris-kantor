<?php
$db = Database::getInstance()->getConnection();

// Get statistics
$totalInventaris = $db->query("SELECT COUNT(*) as total FROM inventaris")->fetch_assoc()['total'];
$totalPeminjaman = $db->query("SELECT COUNT(*) as total FROM peminjaman WHERE status != 'dibatalkan'")->fetch_assoc()['total'];
$peminjamanAktif = $db->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'disetujui'")->fetch_assoc()['total'];
$peminjamanTerlambat = $db->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat'")->fetch_assoc()['total'];
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $totalInventaris; ?></h3>
            <p>Total Inventaris</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $totalPeminjaman; ?></h3>
            <p>Total Peminjaman</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $peminjamanAktif; ?></h3>
            <p>Peminjaman Aktif</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-details">
            <h3><?php echo $peminjamanTerlambat; ?></h3>
            <p>Terlambat</p>
        </div>
    </div>
</div>