<?php
session_start();
require "config/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$stmt = mysqli_prepare($koneksi, "SELECT * FROM penyewaan WHERE user_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$pesanan = mysqli_stmt_get_result($stmt);

$warna = [
    'pending'      => 'warning',
    'disetujui'    => 'info',
    'dipinjam'     => 'primary',
    'dikembalikan' => 'success',
    'ditolak'      => 'danger',
];

$judul = "Riwayat Sewa";
include "includes/header.php";
?>
  <div class="container py-4">
    <h3 class="mb-3">Riwayat Sewa</h3>
    <?php if (isset($_GET['sukses'])) { ?>
      <div class="alert alert-success">Pesanan berhasil dibuat! Tunggu konfirmasi dari admin.</div>
    <?php } ?>

    <?php if (mysqli_num_rows($pesanan) === 0) { ?>
      <div class="alert alert-info">Belum ada pesanan. <a href="katalog.php">Mulai sewa alat</a></div>
    <?php } ?>

    <?php while ($p = mysqli_fetch_assoc($pesanan)) {
        $d = mysqli_prepare($koneksi, "SELECT d.*, a.nama_alat FROM detail_penyewaan d JOIN alat a ON d.alat_id = a.id WHERE d.penyewaan_id = ?");
        mysqli_stmt_bind_param($d, "i", $p['id']);
        mysqli_stmt_execute($d);
        $detail = mysqli_stmt_get_result($d);
    ?>
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between">
          <span>Pesanan #<?= $p['id'] ?> &middot; <?= date("d M Y", strtotime($p['tgl_sewa'])) ?> s/d <?= date("d M Y", strtotime($p['tgl_kembali'])) ?></span>
          <span class="badge bg-<?= $warna[$p['status']] ?>"><?= ucfirst($p['status']) ?></span>
        </div>
        <ul class="list-group list-group-flush">
          <?php while ($row = mysqli_fetch_assoc($detail)) { ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?= htmlspecialchars($row['nama_alat']) ?> &times; <?= $row['jumlah'] ?></span>
              <span>Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></span>
            </li>
          <?php } ?>
        </ul>
        <div class="card-footer text-end fw-bold">Total: Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></div>
      </div>
    <?php } ?>
  </div>
</body>
</html>