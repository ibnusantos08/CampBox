<?php
require "config/koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($koneksi, "SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.kategori_id = k.id WHERE a.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$alat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$judul = $alat ? $alat['nama_alat'] : "Tidak ditemukan";
include "includes/header.php";
?>
  <div class="container py-4" style="max-width:700px">
    <?php if (!$alat) { ?>
      <div class="alert alert-warning">Alat tidak ditemukan. <a href="katalog.php">Kembali ke katalog</a></div>
    <?php } else { ?>
      <a href="katalog.php" class="text-decoration-none">&larr; Kembali ke katalog</a>
      <div class="card mt-3">
        <?= tampil_gambar($alat['gambar'], $alat['nama_alat'], 300) ?>
        <div class="card-body">
          <span class="badge bg-secondary"><?= htmlspecialchars($alat['nama_kategori']) ?></span>
          <h3 class="mt-2"><?= htmlspecialchars($alat['nama_alat']) ?></h3>
          <p><?= nl2br(htmlspecialchars($alat['deskripsi'])) ?></p>
          <h4 class="text-success">Rp <?= number_format($alat['harga_per_hari'], 0, ',', '.') ?> / hari</h4>
          <p class="text-muted">Stok tersedia: <?= $alat['stok'] ?></p>

          <?php if ($alat['stok'] > 0) { ?>
            <form action="keranjang.php" method="POST" class="row g-2 align-items-end">
              <input type="hidden" name="aksi" value="tambah">
              <input type="hidden" name="id" value="<?= $alat['id'] ?>">
              <div class="col-4">
                <label class="form-label">Jumlah</label>
                <input type="number" name="jumlah" class="form-control" value="1" min="1" max="<?= $alat['stok'] ?>">
              </div>
              <div class="col-8">
                <button class="btn btn-success w-100">Tambah ke Keranjang</button>
              </div>
            </form>
          <?php } else { ?>
            <div class="alert alert-danger mb-0">Stok sedang habis.</div>
          <?php } ?>
        </div>
      </div>
    <?php } ?>
  </div>
</body>
</html>