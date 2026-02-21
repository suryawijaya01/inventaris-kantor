<?php
$pageTitle = 'Detail Peminjaman';
$breadcrumbs = [
    ['title' => 'Peminjaman', 'url' => 'index.php'],
    ['title' => 'Detail Peminjaman', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();

// Get ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get peminjaman data
$query = "SELECT p.*, u.username, u.departemen 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = $id";

// If pegawai, only show their own
if (!$auth->isAdmin()) {
    $query .= " AND p.user_id = " . $user['id'];
}

$result = $db->query($query);

if (!$result || $result->num_rows === 0) {
    header('Location: index.php?error=' . urlencode('Data tidak ditemukan'));
    exit();
}

$data = $result->fetch_assoc();

// Get detail items
$detailQuery = "SELECT pd.*, i.nama_inventaris, i.kode_inventaris, i.foto, k.nama_kategori 
                FROM peminjaman_detail pd 
                JOIN inventaris i ON pd.inventaris_id = i.id 
                JOIN kategori k ON i.kategori_id = k.id 
                WHERE pd.peminjaman_id = $id";
$detailResult = $db->query($detailQuery);
?>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topnav.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../views/breadcrumb.php'; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <?php echo $func->getAlert('success', $_GET['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <?php echo $func->getAlert('danger', $_GET['error']); ?>
    <?php endif; ?>

    <?php if ($data['tanggal_kembali_aktual']): ?>
    <tr>
        <td style="padding: 8px 0; font-weight: 600;">Kondisi Pengembalian</td>
        <td style="padding: 8px 0;">
            : 
            <?php 
            // Extract kondisi from catatan if exists
            if (strpos($data['catatan'], '--- CATATAN PENGEMBALIAN ---') !== false) {
                echo '<div style="margin-top: 5px; padding: 10px; background: #f8f9fa; border-radius: 5px; white-space: pre-line;">';
                echo htmlspecialchars(substr($data['catatan'], strpos($data['catatan'], '--- CATATAN PENGEMBALIAN ---')));
                echo '</div>';
            } else {
                echo 'Tidak ada catatan pengembalian';
            }
            ?>
        </td>
    </tr>
<?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-info-circle"></i> Detail Peminjaman</h5>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600; width: 200px;">Nomor Peminjaman</td>
                            <td style="padding: 8px 0;">: <?php echo htmlspecialchars($data['nomor_peminjaman']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Peminjam</td>
                            <td style="padding: 8px 0;">: <?php echo htmlspecialchars($data['username']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Departemen</td>
                            <td style="padding: 8px 0;">: <?php echo $data['departemen'] ? htmlspecialchars($data['departemen']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Status</td>
                            <td style="padding: 8px 0;">: <?php echo $func->getStatusBadge($data['status']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div>
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600; width: 200px;">Tanggal Pinjam</td>
                            <td style="padding: 8px 0;">: <?php echo $func->formatTanggal($data['tanggal_pinjam']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Tanggal Kembali Rencana</td>
                            <td style="padding: 8px 0;">: <?php echo $func->formatTanggal($data['tanggal_kembali_rencana']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Tanggal Kembali Aktual</td>
                            <td style="padding: 8px 0;">: <?php echo $data['tanggal_kembali_aktual'] ? $func->formatTanggal($data['tanggal_kembali_aktual']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: 600;">Catatan</td>
                            <td style="padding: 8px 0;">: <?php echo $data['catatan'] ? htmlspecialchars($data['catatan']) : '-'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 2px solid #ecf0f1;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h5><i class="fas fa-box"></i> Item yang Dipinjam</h5>
                <?php if ($data['status'] == 'diajukan' && !$auth->isAdmin()): ?>
                    <a href="detailadd.php?peminjaman_id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah Item
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Kode</th>
                            <th>Nama Inventaris</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <?php if ($data['status'] == 'diajukan' && !$auth->isAdmin()): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($detailResult && $detailResult->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($detail = $detailResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <?php if ($detail['foto']): ?>
                                            <img src="<?php echo $basePath; ?>/uploads/<?php echo htmlspecialchars($detail['foto']); ?>" 
                                                 alt="Foto" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; cursor: pointer;"
                                                 onclick="window.open('<?php echo $basePath; ?>/uploads/<?php echo htmlspecialchars($detail['foto']); ?>', '_blank')">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #ecf0f1; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: #95a5a6;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($detail['kode_inventaris']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($detail['nama_inventaris']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['nama_kategori']); ?></td>
                                    <td><span class="badge bg-info"><?php echo $detail['jumlah']; ?></span></td>
                                    <?php if ($data['status'] == 'diajukan' && !$auth->isAdmin()): ?>
                                        <td>
                                            <a href="detaildelete.php?id=<?php echo $detail['id']; ?>&peminjaman_id=<?php echo $id; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirmDelete('Hapus item ini dari peminjaman?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo ($data['status'] == 'diajukan' && !$auth->isAdmin()) ? '7' : '6'; ?>" 
                                    style="text-align: center; color: #7f8c8d;">
                                    Belum ada item
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($data['status'] == 'diajukan'): ?>
                <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 6px;">
                    <p style="margin: 0; color: #856404;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Status: Menunggu Persetujuan</strong>
                        <?php if ($auth->isAdmin()): ?>
                            - Silakan review dan setujui atau tolak peminjaman ini.
                        <?php else: ?>
                            - Peminjaman Anda sedang menunggu persetujuan dari admin.
                        <?php endif; ?>
                    </p>
                </div>
            <?php elseif ($data['status'] == 'disetujui'): ?>
                <div style="margin-top: 30px; padding: 15px; background: #d4edda; border-left: 4px solid #27ae60; border-radius: 6px;">
                    <p style="margin: 0; color: #155724;">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Status: Disetujui</strong> - Peminjaman telah disetujui. Harap kembalikan barang sebelum tanggal yang ditentukan.
                    </p>
                </div>
            <?php elseif ($data['status'] == 'terlambat'): ?>
                <div style="margin-top: 30px; padding: 15px; background: #f8d7da; border-left: 4px solid #e74c3c; border-radius: 6px;">
                    <p style="margin: 0; color: #721c24;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Status: Terlambat</strong> - Pengembalian melebihi batas waktu yang ditentukan.
                    </p>
                </div>
            <?php elseif ($data['status'] == 'dikembalikan'): ?>
                <div style="margin-top: 30px; padding: 15px; background: #d1ecf1; border-left: 4px solid #16a085; border-radius: 6px;">
                    <p style="margin: 0; color: #0c5460;">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Status: Dikembalikan</strong> - Barang telah dikembalikan dengan baik.
                    </p>
                </div>
            <?php elseif ($data['status'] == 'dibatalkan'): ?>
                <div style="margin-top: 30px; padding: 15px; background: #e2e3e5; border-left: 4px solid #6c757d; border-radius: 6px;">
                    <p style="margin: 0; color: #383d41;">
                        <i class="fas fa-ban"></i> 
                        <strong>Status: Dibatalkan</strong> - Peminjaman telah dibatalkan.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>