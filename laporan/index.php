<?php
$pageTitle = 'Laporan';
$breadcrumbs = [
    ['title' => 'Laporan', 'url' => '#']
];

require_once __DIR__ . '/../views/header.php';

// Only admin can access
$auth->requireAdmin();

$db = Database::getInstance()->getConnection();

// Get filter parameters
$tipe_laporan = isset($_GET['tipe']) ? $_GET['tipe'] : 'inventaris';
$tanggal_dari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : date('Y-m-01');
$tanggal_sampai = isset($_GET['tanggal_sampai']) ? $_GET['tanggal_sampai'] : date('Y-m-d');
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Get categories for filter
$categories = $func->getKategori();
?>

<?php include __DIR__ . '/../views/sidebar.php'; ?>
<?php include __DIR__ . '/../views/topnav.php'; ?>

<div class="main-content">
    <?php include __DIR__ . '/../views/breadcrumb.php'; ?>
    
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-chart-bar"></i> Laporan</h5>
        </div>
        <div class="card-body">
            <!-- Filter Form -->
            <form method="GET" action="" id="filterForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Tipe Laporan</label>
                        <select name="tipe" class="form-control" onchange="toggleFilters()" style="padding: 12px 15px;">
                            <option value="inventaris" <?php echo $tipe_laporan == 'inventaris' ? 'selected' : ''; ?>>Laporan Inventaris</option>
                            <option value="peminjaman" <?php echo $tipe_laporan == 'peminjaman' ? 'selected' : ''; ?>>Laporan Peminjaman</option>
                            <option value="stok" <?php echo $tipe_laporan == 'stok' ? 'selected' : ''; ?>>Laporan Stok</option>
                            <option value="kategori" <?php echo $tipe_laporan == 'kategori' ? 'selected' : ''; ?>>Laporan Per Kategori</option>
                            <option value="user" <?php echo $tipe_laporan == 'user' ? 'selected' : ''; ?>>Laporan Per User</option>
                        </select>
                    </div>
                    
                    <div class="form-group date-filter" style="margin-bottom: 0;">
                        <label>Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="<?php echo $tanggal_dari; ?>">
                    </div>
                    
                    <div class="form-group date-filter" style="margin-bottom: 0;">
                        <label>Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="<?php echo $tanggal_sampai; ?>">
                    </div>
                    
                    <div class="form-group kategori-filter" style="margin-bottom: 0;">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control" style="padding: 12px 15px;">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $kategori_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group status-filter" style="margin-bottom: 0;">
                        <label>Status</label>
                        <select name="status" class="form-control" style="padding: 12px 15px;">
                            <option value="">Semua Status</option>
                            <option value="diajukan" <?php echo $status_filter == 'diajukan' ? 'selected' : ''; ?>>Diajukan</option>
                            <option value="disetujui" <?php echo $status_filter == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                            <option value="dikembalikan" <?php echo $status_filter == 'dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
                            <option value="terlambat" <?php echo $status_filter == 'terlambat' ? 'selected' : ''; ?>>Terlambat</option>
                            <option value="dibatalkan" <?php echo $status_filter == 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Tampilkan Laporan
                    </button>
                    <button type="button" class="btn btn-success" onclick="printReport()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <button type="button" class="btn btn-info" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Report Content -->
    <div class="card" id="reportContent">
        <div class="card-header">
            <h5>
                <i class="fas fa-file-alt"></i> 
                <?php 
                switch($tipe_laporan) {
                    case 'inventaris': echo 'Laporan Data Inventaris'; break;
                    case 'peminjaman': echo 'Laporan Peminjaman'; break;
                    case 'stok': echo 'Laporan Stok Inventaris'; break;
                    case 'kategori': echo 'Laporan Per Kategori'; break;
                    case 'user': echo 'Laporan Per User'; break;
                }
                ?>
            </h5>
            <div>
                <small style="color: rgba(255,255,255,0.8);">
                    Periode: <?php echo $func->formatTanggal($tanggal_dari); ?> - <?php echo $func->formatTanggal($tanggal_sampai); ?>
                </small>
            </div>
        </div>
        <div class="card-body">
            <?php if ($tipe_laporan == 'inventaris'): ?>
                <!-- Laporan Inventaris -->
                <?php
                $query = "SELECT i.*, k.nama_kategori 
                          FROM inventaris i 
                          JOIN kategori k ON i.kategori_id = k.id";
                
                if ($kategori_filter > 0) {
                    $query .= " WHERE i.kategori_id = $kategori_filter";
                }
                
                $query .= " ORDER BY i.nama_inventaris ASC";
                $result = $db->query($query);
                
                $total_items = 0;
                $total_stok = 0;
                ?>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Inventaris</th>
                                <th>Kategori</th>
                                <th>Jumlah Tersedia</th>
                                <th>Status Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php 
                                    $total_items++;
                                    $total_stok += $row['jumlah_tersedia'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['kode_inventaris']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['nama_inventaris']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td><?php echo $row['jumlah_tersedia']; ?></td>
                                        <td>
                                            <?php if ($row['jumlah_tersedia'] == 0): ?>
                                                <span class="badge bg-danger">Habis</span>
                                            <?php elseif ($row['jumlah_tersedia'] <= 5): ?>
                                                <span class="badge bg-warning">Menipis</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aman</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="4" style="text-align: right;">TOTAL:</td>
                                    <td><?php echo $total_stok; ?></td>
                                    <td><?php echo $total_items; ?> Item</td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #7f8c8d;">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php elseif ($tipe_laporan == 'peminjaman'): ?>
                <!-- Laporan Peminjaman -->
                <?php
                $query = "SELECT p.*, u.username, u.departemen 
                          FROM peminjaman p 
                          JOIN users u ON p.user_id = u.id 
                          WHERE p.tanggal_pinjam BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";
                
                if (!empty($status_filter)) {
                    $query .= " AND p.status = '$status_filter'";
                }
                
                $query .= " ORDER BY p.tanggal_pinjam DESC";
                $result = $db->query($query);
                
                $total_peminjaman = 0;
                $status_count = ['diajukan' => 0, 'disetujui' => 0, 'dikembalikan' => 0, 'terlambat' => 0, 'dibatalkan' => 0];
                ?>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. Peminjaman</th>
                                <th>Peminjam</th>
                                <th>Departemen</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php 
                                    $total_peminjaman++;
                                    $status_count[$row['status']]++;
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['nomor_peminjaman']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo $row['departemen'] ? htmlspecialchars($row['departemen']) : '-'; ?></td>
                                        <td><?php echo $func->formatTanggal($row['tanggal_pinjam']); ?></td>
                                        <td>
                                            <?php echo $row['tanggal_kembali_aktual'] ? $func->formatTanggal($row['tanggal_kembali_aktual']) : $func->formatTanggal($row['tanggal_kembali_rencana']); ?>
                                        </td>
                                        <td><?php echo $func->getStatusBadge($row['status']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #7f8c8d;">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_peminjaman > 0): ?>
                    <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h6 style="margin-bottom: 15px;"><i class="fas fa-chart-pie"></i> Ringkasan:</h6>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                            <div>
                                <strong>Total Peminjaman:</strong><br>
                                <span class="badge bg-info" style="font-size: 14px; padding: 8px 12px;"><?php echo $total_peminjaman; ?></span>
                            </div>
                            <div>
                                <strong>Diajukan:</strong><br>
                                <span class="badge bg-warning" style="font-size: 14px; padding: 8px 12px;"><?php echo $status_count['diajukan']; ?></span>
                            </div>
                            <div>
                                <strong>Disetujui:</strong><br>
                                <span class="badge bg-success" style="font-size: 14px; padding: 8px 12px;"><?php echo $status_count['disetujui']; ?></span>
                            </div>
                            <div>
                                <strong>Dikembalikan:</strong><br>
                                <span class="badge bg-info" style="font-size: 14px; padding: 8px 12px;"><?php echo $status_count['dikembalikan']; ?></span>
                            </div>
                            <div>
                                <strong>Terlambat:</strong><br>
                                <span class="badge bg-danger" style="font-size: 14px; padding: 8px 12px;"><?php echo $status_count['terlambat']; ?></span>
                            </div>
                            <div>
                                <strong>Dibatalkan:</strong><br>
                                <span class="badge bg-secondary" style="font-size: 14px; padding: 8px 12px;"><?php echo $status_count['dibatalkan']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($tipe_laporan == 'stok'): ?>
                <!-- Laporan Stok -->
                <?php
                $query = "SELECT i.*, k.nama_kategori,
                          (SELECT COALESCE(SUM(pd.jumlah), 0) FROM peminjaman_detail pd 
                           JOIN peminjaman p ON pd.peminjaman_id = p.id 
                           WHERE pd.inventaris_id = i.id AND p.status IN ('disetujui', 'terlambat')) as jumlah_dipinjam
                          FROM inventaris i 
                          JOIN kategori k ON i.kategori_id = k.id";
                
                if ($kategori_filter > 0) {
                    $query .= " WHERE i.kategori_id = $kategori_filter";
                }
                
                $query .= " ORDER BY i.jumlah_tersedia ASC";
                $result = $db->query($query);
                ?>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Inventaris</th>
                                <th>Kategori</th>
                                <th>Stok Tersedia</th>
                                <th>Sedang Dipinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['kode_inventaris']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['nama_inventaris']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td><?php echo $row['jumlah_tersedia']; ?></td>
                                        <td><?php echo $row['jumlah_dipinjam']; ?></td>
                                        <td>
                                            <?php if ($row['jumlah_tersedia'] == 0): ?>
                                                <span class="badge bg-danger">Habis</span>
                                            <?php elseif ($row['jumlah_tersedia'] <= 5): ?>
                                                <span class="badge bg-warning">Menipis</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aman</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #7f8c8d;">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php elseif ($tipe_laporan == 'kategori'): ?>
                <!-- Laporan Per Kategori -->
                <?php
                $query = "SELECT k.nama_kategori,
                          COUNT(i.id) as total_inventaris,
                          SUM(i.jumlah_tersedia) as total_stok,
                          SUM(CASE WHEN i.jumlah_tersedia = 0 THEN 1 ELSE 0 END) as stok_habis,
                          SUM(CASE WHEN i.jumlah_tersedia <= 5 AND i.jumlah_tersedia > 0 THEN 1 ELSE 0 END) as stok_menipis
                          FROM kategori k
                          LEFT JOIN inventaris i ON k.id = i.kategori_id";
                
                if ($kategori_filter > 0) {
                    $query .= " WHERE k.id = $kategori_filter";
                }
                
                $query .= " GROUP BY k.id, k.nama_kategori ORDER BY k.nama_kategori ASC";
                $result = $db->query($query);
                ?>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kategori</th>
                                <th>Total Item</th>
                                <th>Total Stok</th>
                                <th>Stok Habis</th>
                                <th>Stok Menipis</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php 
                                $grand_total_items = 0;
                                $grand_total_stok = 0;
                                $grand_stok_habis = 0;
                                $grand_stok_menipis = 0;
                                ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $grand_total_items += $row['total_inventaris'];
                                    $grand_total_stok += $row['total_stok'];
                                    $grand_stok_habis += $row['stok_habis'];
                                    $grand_stok_menipis += $row['stok_menipis'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['nama_kategori']); ?></strong></td>
                                        <td><?php echo $row['total_inventaris']; ?></td>
                                        <td><?php echo $row['total_stok'] ?: 0; ?></td>
                                        <td><span class="badge bg-danger"><?php echo $row['stok_habis']; ?></span></td>
                                        <td><span class="badge bg-warning"><?php echo $row['stok_menipis']; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="2" style="text-align: right;">TOTAL:</td>
                                    <td><?php echo $grand_total_items; ?></td>
                                    <td><?php echo $grand_total_stok; ?></td>
                                    <td><span class="badge bg-danger"><?php echo $grand_stok_habis; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $grand_stok_menipis; ?></span></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #7f8c8d;">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php elseif ($tipe_laporan == 'user'): ?>
                <!-- Laporan Per User -->
                <?php
                $query = "SELECT u.username, u.departemen, u.role,
                          COUNT(p.id) as total_peminjaman,
                          SUM(CASE WHEN p.status = 'disetujui' THEN 1 ELSE 0 END) as aktif,
                          SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END) as selesai,
                          SUM(CASE WHEN p.status = 'terlambat' THEN 1 ELSE 0 END) as terlambat
                          FROM users u
                          LEFT JOIN peminjaman p ON u.id = p.user_id 
                              AND p.tanggal_pinjam BETWEEN '$tanggal_dari' AND '$tanggal_sampai'
                          WHERE u.role = 'pegawai'
                          GROUP BY u.id, u.username, u.departemen, u.role
                          ORDER BY total_peminjaman DESC";
                $result = $db->query($query);
                ?>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Departemen</th>
                                <th>Total Peminjaman</th>
                                <th>Aktif</th>
                                <th>Selesai</th>
                                <th>Terlambat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php $no = 1; ?>
                                <?php 
                                $grand_total = 0;
                                $grand_aktif = 0;
                                $grand_selesai = 0;
                                $grand_terlambat = 0;
                                ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $grand_total += $row['total_peminjaman'];
                                    $grand_aktif += $row['aktif'];
                                    $grand_selesai += $row['selesai'];
                                    $grand_terlambat += $row['terlambat'];
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                                        <td><?php echo $row['departemen'] ? htmlspecialchars($row['departemen']) : '-'; ?></td>
                                        <td><?php echo $row['total_peminjaman']; ?></td>
                                        <td><span class="badge bg-success"><?php echo $row['aktif']; ?></span></td>
                                        <td><span class="badge bg-info"><?php echo $row['selesai']; ?></span></td>
                                        <td><span class="badge bg-danger"><?php echo $row['terlambat']; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                                    <td><?php echo $grand_total; ?></td>
                                    <td><span class="badge bg-success"><?php echo $grand_aktif; ?></span></td>
                                    <td><span class="badge bg-info"><?php echo $grand_selesai; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo $grand_terlambat; ?></span></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #7f8c8d;">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleFilters() {
        const tipe = document.querySelector('select[name="tipe"]').value;
        const dateFilters = document.querySelectorAll('.date-filter');
        const kategoriFilter = document.querySelector('.kategori-filter');
        const statusFilter = document.querySelector('.status-filter');
        
        // Hide all filters first
        dateFilters.forEach(f => f.style.display = 'none');
        kategoriFilter.style.display = 'none';
        statusFilter.style.display = 'none';
        
        // Show relevant filters based on report type
        if (tipe === 'peminjaman' || tipe === 'user') {
            dateFilters.forEach(f => f.style.display = 'block');
        }
        
        if (tipe === 'peminjaman') {
            statusFilter.style.display = 'block';
        }
        
        if (tipe === 'inventaris' || tipe === 'stok' || tipe === 'kategori') {
            kategoriFilter.style.display = 'block';
        }
    }
    
    function printReport() {
        const reportContent = document.getElementById('reportContent').innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        
        printWindow.document.write('<html><head><title>Cetak Laporan</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; padding: 20px; }');
        printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
        printWindow.document.write('th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }');
        printWindow.document.write('th { background-color: #2c3e50; color: white; }');
        printWindow.document.write('.badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; }');
        printWindow.document.write('.bg-success { background: #27ae60; color: white; }');
        printWindow.document.write('.bg-danger { background: #e74c3c; color: white; }');
        printWindow.document.write('.bg-warning { background: #f39c12; color: white; }');
        printWindow.document.write('.bg-info { background: #16a085; color: white; }');
        printWindow.document.write('.bg-secondary { background: #95a5a6; color: white; }');
        printWindow.document.write('.card-header { background: #2c3e50; color: white; padding: 15px; margin-bottom: 20px; }');
        printWindow.document.write('@media print { .no-print { display: none; } }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h2>SISTEM INVENTARIS KANTOR</h2>');
        printWindow.document.write('<p>Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID') + '</p>');
        printWindow.document.write(reportContent);
        printWindow.document.write('</body></html>');
        
        printWindow.document.close();
        printWindow.print();
    }
    
    function exportExcel() {
        const table = document.querySelector('#reportContent table');
        if (!table) {
            alert('Tidak ada data untuk diexport');
            return;
        }
        
        let html = table.outerHTML;
        const url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'laporan_' +
        new Date().getTime() + '.xls';
        link.click();
    }

    // Initialize filters on page load
    document.addEventListener('DOMContentLoaded', function() {
    toggleFilters();
    });
</script>
<?php include __DIR__ . '/../views/footer.php'; ?>