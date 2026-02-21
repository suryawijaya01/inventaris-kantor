<?php
$pageTitle = 'Data Peminjaman';
$breadcrumbs = [
    ['title' => 'Peminjaman', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

$db = Database::getInstance()->getConnection();

// Get search parameter
$search = isset($_GET['search']) ? $db->real_escape_string($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $db->real_escape_string($_GET['status']) : '';

// Build query with search - admin sees all, pegawai only sees their own
$query = "SELECT p.*, u.username 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id";

// If pegawai, only show their borrowings
if (!$auth->isAdmin()) {
    $query .= " WHERE p.user_id = " . $user['id'];
    if (!empty($statusFilter)) {
        $query .= " AND p.status = '$statusFilter'";
    }
    if (!empty($search)) {
        $query .= " AND (p.nomor_peminjaman LIKE '%$search%' OR p.status LIKE '%$search%')";
    }
} else {
    $conditions = [];
    if (!empty($statusFilter)) {
        $conditions[] = "p.status = '$statusFilter'";
    }
    if (!empty($search)) {
        $conditions[] = "(p.nomor_peminjaman LIKE '%$search%' OR u.username LIKE '%$search%' OR p.status LIKE '%$search%')";
    }
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
}

$query .= " ORDER BY p.id DESC";

$result = $db->query($query);
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
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-exchange-alt"></i> Data Peminjaman</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Peminjaman
            </a>
        </div>
        <div class="card-body">
            <!-- Search Form -->
            <form method="GET" action="" style="margin-bottom: 20px;">
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor peminjaman, peminjam, atau status..." value="<?php echo htmlspecialchars($search); ?>" style="max-width: 350px;">
                    
                    <select name="status" class="form-control" style="max-width: 200px; padding: 12px 15px;">
                        <option value="">Semua Status</option>
                        <option value="diajukan" <?php echo $statusFilter == 'diajukan' ? 'selected' : ''; ?>>Diajukan</option>
                        <option value="disetujui" <?php echo $statusFilter == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="dikembalikan" <?php echo $statusFilter == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                        <option value="terlambat" <?php echo $statusFilter == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                        <option value="dibatalkan" <?php echo $statusFilter == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. Peminjaman</th>
                            <?php if ($auth->isAdmin()): ?>
                                <th>Peminjam</th>
                            <?php endif; ?>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali Rencana</th>
                            <th>Tgl Kembali Aktual</th>
                            <th>Status</th>
                            <th style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['nomor_peminjaman']); ?></strong></td>
                                    <?php if ($auth->isAdmin()): ?>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo $func->formatTanggal($row['tanggal_pinjam']); ?></td>
                                    <td><?php echo $func->formatTanggal($row['tanggal_kembali_rencana']); ?></td>
                                    <td>
                                        <?php echo $row['tanggal_kembali_aktual'] ? $func->formatTanggal($row['tanggal_kembali_aktual']) : '-'; ?>
                                    </td>
                                    <td><?php echo $func->getStatusBadge($row['status']); ?></td>
                                    <td>
                                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($row['status'] == 'diajukan'): ?>
                                            <?php if ($auth->isAdmin()): ?>
                                                <a href="edit.php?id=<?php echo $row['id']; ?>&action=approve" class="btn btn-sm btn-success" title="Setujui" onclick="return confirm('Setujui peminjaman ini?')">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $row['id']; ?>&action=reject" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Tolak peminjaman ini?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Batalkan peminjaman ini?')" title="Batalkan">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($row['status'] == 'disetujui' && $auth->isAdmin()): ?>
                                            <button type="button" class="btn btn-sm btn-warning" title="Kembalikan" onclick="openReturnModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nomor_peminjaman']); ?>', '<?php echo $row['tanggal_kembali_rencana']; ?>')">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $auth->isAdmin() ? '8' : '7'; ?>" style="text-align: center; color: #7f8c8d;">
                                    <?php echo !empty($search) || !empty($statusFilter) ? 'Data tidak ditemukan' : 'Belum ada data peminjaman'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pengembalian -->
<div id="returnModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 10px; padding: 30px; max-width: 500px; width: 90%;">
        <h5 style="margin-bottom: 20px; color: #2c3e50;">
            <i class="fas fa-undo"></i> Pengembalian Barang
        </h5>
        
        <form id="returnForm" method="POST" action="return.php">
            <input type="hidden" name="peminjaman_id" id="return_peminjaman_id">
            
            <div class="form-group">
                <label><strong>Nomor Peminjaman:</strong></label>
                <input type="text" id="return_nomor" class="form-control" readonly style="background: #f8f9fa;">
            </div>
            
            <div class="form-group">
                <label><strong>Tanggal Kembali Rencana:</strong></label>
                <input type="text" id="return_rencana" class="form-control" readonly style="background: #f8f9fa;">
            </div>
            
            <div class="form-group">
                <label>Tanggal Pengembalian Aktual <span style="color: red;">*</span></label>
                <input type="date" name="tanggal_kembali_aktual" id="tanggal_kembali_aktual" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                <small style="color: #7f8c8d;">Tanggal saat barang dikembalikan</small>
            </div>
            
            <div class="form-group">
                <label>Kondisi Barang</label>
                <select name="kondisi_barang" class="form-control" style="padding: 12px 15px;">
                    <option value="Baik">Baik - Tidak ada kerusakan</option>
                    <option value="Rusak Ringan">Rusak Ringan - Ada kerusakan kecil</option>
                    <option value="Rusak Berat">Rusak Berat - Kerusakan signifikan</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Catatan Pengembalian</label>
                <textarea name="catatan_pengembalian" class="form-control" rows="3" placeholder="Catatan kondisi atau keterangan lainnya..."></textarea>
            </div>
            
            <div id="lateWarning" style="display: none; padding: 15px; background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 6px; margin-bottom: 15px;">
                <p style="margin: 0; color: #856404;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Peringatan:</strong> Pengembalian ini <strong id="lateDays"></strong> dari tanggal yang ditentukan.
                </p>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fas fa-save"></i> Proses Pengembalian
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeReturnModal()" style="flex: 1;">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReturnModal(id, nomor, tanggalRencana) {
        document.getElementById('return_peminjaman_id').value = id;
        document.getElementById('return_nomor').value = nomor;
        document.getElementById('return_rencana').value = formatDate(tanggalRencana);
        document.getElementById('returnModal').style.display = 'flex';
        
        // Check if late
        checkLateReturn(tanggalRencana);
    }
    
    function closeReturnModal() {
        document.getElementById('returnModal').style.display = 'none';
        document.getElementById('returnForm').reset();
    }
    
    function formatDate(dateString) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                       'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const parts = dateString.split('-');
        return parts[2] + ' ' + months[parseInt(parts[1]) - 1] + ' ' + parts[0];
    }
    
    function checkLateReturn(tanggalRencana) {
        const tanggalKembali = document.getElementById('tanggal_kembali_aktual').value;
        const rencana = new Date(tanggalRencana);
        const aktual = new Date(tanggalKembali);
        
        if (aktual > rencana) {
            const diffTime = Math.abs(aktual - rencana);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('lateDays').textContent = 'terlambat ' + diffDays + ' hari';
            document.getElementById('lateWarning').style.display = 'block';
        } else {
            document.getElementById('lateWarning').style.display = 'none';
        }
    }
    
    // Add event listener to date input
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('tanggal_kembali_aktual');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                const rencana = document.getElementById('return_rencana').dataset.original;
                if (rencana) {
                    checkLateReturn(rencana);
                }
            });
        }
    });
</script>

<?php include __DIR__ . '/../views/footer.php'; ?>