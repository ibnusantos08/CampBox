<?php
require "config/koneksi.php";
$judul = "Beranda";
include "includes/header.php";

$hasil = mysqli_query($koneksi, "SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.kategori_id = k.id ORDER BY a.id");
?>
  <div class="container py-4">
    <h3 class="mb-3">Alat Tersedia</h3>
    <div class="row g-3">
      <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
        <div class="col-md-4 col-lg-3">
          <div class="card h-100">
            <?= tampil_gambar($row['gambar'], $row['nama_alat']) ?>
            <div class="card-body">
              <span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span>
              <h5 class="card-title mt-2"><?= htmlspecialchars($row['nama_alat']) ?></h5>
              <p class="card-text small"><?= htmlspecialchars($row['deskripsi']) ?></p>
              <p class="fw-bold text-success mb-0">Rp <?= number_format($row['harga_per_hari'], 0, ',', '.') ?> / hari</p>
              <small class="text-muted">Stok: <?= $row['stok'] ?></small>
            </div>
            <div class="card-footer bg-white border-0">
              <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-success btn-sm w-100">Lihat Detail</a>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</body>
</html>