<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-info-circle"></i> Selamat Datang</h5>
    </div>
    <div class="card-body">
        <h4>Halo, <?php echo htmlspecialchars($user['username']); ?>! 👋</h4>
        <p>Selamat datang di Sistem Manajemen Inventaris Kantor. Gunakan menu di sebelah kiri untuk mengelola data inventaris dan peminjaman.</p>
        
        <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--accent);">
            <h5 style="margin-top: 0;"><i class="fas fa-lightbulb"></i> Tips Penggunaan:</h5>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Pastikan data inventaris selalu ter-update</li>
                <li>Periksa peminjaman yang terlambat secara berkala</li>
                <li>Monitor stok barang yang menipis</li>
                <li>Gunakan fitur pencarian untuk menemukan data dengan cepat</li>
            </ul>
        </div>
    </div>
</div>