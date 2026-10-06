<?php
require "config/koneksi.php";
$judul = "Katalog";
include "includes/header.php";

// ambil semua kategori untuk filter
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");

$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$cari        = isset($_GET['q']) ? trim($_GET['q']) : "";
$like        = "%" . $cari . "%";

if ($kategori_id > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.kategori_id = k.id WHERE a.kategori_id = ? AND a.nama_alat LIKE ? ORDER BY a.nama_alat");
    mysqli_stmt_bind_param($stmt, "is", $kategori_id, $like);
} else {
    $stmt = mysqli_prepare($koneksi, "SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.kategori_id = k.id WHERE a.nama_alat LIKE ? ORDER BY a.nama_alat");
    mysqli_stmt_bind_param($stmt, "s", $like);
}
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
?>
  <div class="container py-4">
    <h3 class="mb-3">Katalog Alat</h3>

    <form method="GET" class="row g-2 mb-4">
      <div class="col-md-5">
        <input type="text" name="q" class="form-control" placeholder="Cari alat..." value="<?= htmlspecialchars($cari) ?>">
      </div>
      <div class="col-md-4">
        <select name="kategori" class="form-select">
          <option value="0">Semua Kategori</option>
          <?php while ($k = mysqli_fetch_assoc($kategori)) { ?>
            <option value="<?= $k['id'] ?>" <?= $kategori_id == $k['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-success w-100">Filter</button>
      </div>
    </form>

    <div class="row g-3">
      <?php if (mysqli_num_rows($hasil) === 0) { ?>
        <p class="text-muted">Alat tidak ditemukan.</p>
      <?php } ?>
      <?php while ($row = mysqli_fetch_assoc($hasil)) { ?>
        <div class="col-md-4 col-lg-3">
          <div class="card h-100">
            <?= tampil_gambar($row['gambar'], $row['nama_alat']) ?>
            <div class="card-body">
              <span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span>
              <h5 class="card-title mt-2"><?= htmlspecialchars($row['nama_alat']) ?></h5>
              <p class="fw-bold text-success mb-0">Rp <?= number_format($row['harga_per_hari'], 0, ',', '.') ?> / hari</p>
              <small class="<?= $row['stok'] > 0 ? 'text-muted' : 'text-danger' ?>">
                <?= $row['stok'] > 0 ? 'Stok: ' . $row['stok'] : 'Stok habis' ?>
              </small>
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