<?php
session_start();
require "config/koneksi.php";

// harus login dulu
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? "";
    $id   = (int)($_POST['id'] ?? 0);

    if ($aksi === "tambah" || $aksi === "ubah") {
        $jumlah = max(1, (int)($_POST['jumlah'] ?? 1));

        // cek stok di database
        $stmt = mysqli_prepare($koneksi, "SELECT stok FROM alat WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($row) {
            $sudah = ($aksi === "tambah") ? ($_SESSION['keranjang'][$id] ?? 0) : 0;
            $total = $sudah + $jumlah;
            if ($total > $row['stok']) {
                $total = $row['stok'];
                $pesan = "Jumlah disesuaikan dengan stok yang tersedia.";
            }
            if ($total > 0) {
                $_SESSION['keranjang'][$id] = $total;
            }
        }
    } elseif ($aksi === "hapus") {
        unset($_SESSION['keranjang'][$id]);
    } elseif ($aksi === "kosongkan") {
        $_SESSION['keranjang'] = [];
    }

    // PRG: hindari submit ulang saat refresh
    if ($pesan === "") {
        header("Location: keranjang.php");
        exit;
    }
}

// ambil data alat yang ada di keranjang
$items = [];
$total_per_hari = 0;
if (!empty($_SESSION['keranjang'])) {
    $ids = array_map('intval', array_keys($_SESSION['keranjang']));
    $daftar = implode(",", $ids);
    $q = mysqli_query($koneksi, "SELECT * FROM alat WHERE id IN ($daftar)");
    while ($r = mysqli_fetch_assoc($q)) {
        $r['jumlah']   = $_SESSION['keranjang'][$r['id']];
        $r['subtotal'] = $r['jumlah'] * $r['harga_per_hari'];
        $total_per_hari += $r['subtotal'];
        $items[] = $r;
    }
}

$judul = "Keranjang";
include "includes/header.php";
?>
  <div class="container py-4">
    <h3 class="mb-3">Keranjang Sewa</h3>

    <?php if ($pesan) { ?><div class="alert alert-warning"><?= $pesan ?></div><?php } ?>

    <?php if (empty($items)) { ?>
      <div class="alert alert-info">Keranjang masih kosong. <a href="katalog.php">Lihat katalog</a></div>
    <?php } else { ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr><th>Alat</th><th>Harga / hari</th><th style="width:160px">Jumlah</th><th>Subtotal / hari</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it) { ?>
              <tr>
                <td><?= htmlspecialchars($it['nama_alat']) ?></td>
                <td>Rp <?= number_format($it['harga_per_hari'], 0, ',', '.') ?></td>
                <td>
                  <form method="POST" class="d-flex gap-1">
                    <input type="hidden" name="aksi" value="ubah">
                    <input type="hidden" name="id" value="<?= $it['id'] ?>">
                    <input type="number" name="jumlah" class="form-control form-control-sm" value="<?= $it['jumlah'] ?>" min="1" max="<?= $it['stok'] ?>">
                    <button class="btn btn-outline-secondary btn-sm">Ubah</button>
                  </form>
                </td>
                <td>Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= $it['id'] ?>">
                    <button class="btn btn-outline-danger btn-sm">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Total per hari</th>
              <th colspan="2">Rp <?= number_format($total_per_hari, 0, ',', '.') ?></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex justify-content-between">
        <form method="POST">
          <input type="hidden" name="aksi" value="kosongkan">
          <button class="btn btn-outline-danger">Kosongkan Keranjang</button>
        </form>
        <a href="checkout.php" class="btn btn-success">Lanjut ke Checkout</a>
      </div>
    <?php } ?>
  </div>
</body>
</html>