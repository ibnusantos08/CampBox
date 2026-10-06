<?php
session_start();
require "config/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

// ambil data alat di keranjang
$ids    = array_map('intval', array_keys($_SESSION['keranjang']));
$daftar = implode(",", $ids);
$q      = mysqli_query($koneksi, "SELECT * FROM alat WHERE id IN ($daftar)");
$items  = [];
$total_per_hari = 0;
while ($r = mysqli_fetch_assoc($q)) {
    $r['jumlah']   = $_SESSION['keranjang'][$r['id']];
    $r['subtotal'] = $r['jumlah'] * $r['harga_per_hari'];
    $total_per_hari += $r['subtotal'];
    $items[] = $r;
}

$error = "";
$hari_ini = date("Y-m-d");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_sewa    = $_POST['tgl_sewa'] ?? "";
    $tgl_kembali = $_POST['tgl_kembali'] ?? "";

    $d1 = DateTime::createFromFormat("Y-m-d", $tgl_sewa);
    $d2 = DateTime::createFromFormat("Y-m-d", $tgl_kembali);

    if (!$d1 || !$d2) {
        $error = "Tanggal tidak valid.";
    } elseif ($tgl_sewa < $hari_ini) {
        $error = "Tanggal sewa tidak boleh sebelum hari ini.";
    } elseif ($tgl_kembali <= $tgl_sewa) {
        $error = "Tanggal kembali harus setelah tanggal sewa.";
    } else {
        $lama  = $d1->diff($d2)->days;      // lama sewa (hari)
        $total = $total_per_hari * $lama;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            mysqli_begin_transaction($koneksi);

            // cek stok terakhir (dikunci biar aman kalau ada yang checkout bersamaan)
            foreach ($items as $it) {
                $cek = mysqli_prepare($koneksi, "SELECT nama_alat, stok FROM alat WHERE id = ? FOR UPDATE");
                mysqli_stmt_bind_param($cek, "i", $it['id']);
                mysqli_stmt_execute($cek);
                $row = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
                if (!$row || $row['stok'] < $it['jumlah']) {
                    throw new Exception("Stok " . $it['nama_alat'] . " tidak cukup. Silakan ubah jumlah di keranjang.");
                }
            }

            // simpan header transaksi
            $stmt = mysqli_prepare($koneksi, "INSERT INTO penyewaan (user_id, tgl_sewa, tgl_kembali, total_harga) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issi", $_SESSION['user_id'], $tgl_sewa, $tgl_kembali, $total);
            mysqli_stmt_execute($stmt);
            $penyewaan_id = mysqli_insert_id($koneksi);

            // simpan detail + kurangi stok
            $det = mysqli_prepare($koneksi, "INSERT INTO detail_penyewaan (penyewaan_id, alat_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
            $upd = mysqli_prepare($koneksi, "UPDATE alat SET stok = stok - ? WHERE id = ?");
            foreach ($items as $it) {
                $sub = $it['jumlah'] * $it['harga_per_hari'] * $lama;
                mysqli_stmt_bind_param($det, "iiiii", $penyewaan_id, $it['id'], $it['jumlah'], $it['harga_per_hari'], $sub);
                mysqli_stmt_execute($det);

                mysqli_stmt_bind_param($upd, "ii", $it['jumlah'], $it['id']);
                mysqli_stmt_execute($upd);
            }

            mysqli_commit($koneksi);
            $_SESSION['keranjang'] = [];
            header("Location: riwayat.php?sukses=1");
            exit;
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}

$judul = "Checkout";
include "includes/header.php";
?>
  <div class="container py-4" style="max-width:800px">
    <h3 class="mb-3">Checkout</h3>
    <?php if ($error) { ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php } ?>

    <table class="table">
      <thead><tr><th>Alat</th><th>Jumlah</th><th>Harga / hari</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it) { ?>
          <tr>
            <td><?= htmlspecialchars($it['nama_alat']) ?></td>
            <td><?= $it['jumlah'] ?></td>
            <td>Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
          </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr><th colspan="2" class="text-end">Total per hari</th><th>Rp <?= number_format($total_per_hari, 0, ',', '.') ?></th></tr>
      </tfoot>
    </table>

    <form method="POST" class="card card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Tanggal Sewa</label>
          <input type="date" name="tgl_sewa" id="tgl_sewa" class="form-control" min="<?= $hari_ini ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tanggal Kembali</label>
          <input type="date" name="tgl_kembali" id="tgl_kembali" class="form-control" min="<?= $hari_ini ?>" required>
        </div>
      </div>
      <p class="mt-3 mb-1">Lama sewa: <strong id="lama">-</strong> hari</p>
      <p class="mb-3">Total bayar: <strong class="text-success" id="total">-</strong></p>
      <button class="btn btn-success">Buat Pesanan</button>
    </form>
  </div>

<script>
  const perHari = <?= (int)$total_per_hari ?>;
  const sewa = document.getElementById('tgl_sewa');
  const kembali = document.getElementById('tgl_kembali');

  function hitung() {
    if (!sewa.value || !kembali.value) return;
    const lama = Math.round((new Date(kembali.value) - new Date(sewa.value)) / 86400000);
    if (lama > 0) {
      document.getElementById('lama').textContent = lama;
      document.getElementById('total').textContent = 'Rp ' + (lama * perHari).toLocaleString('id-ID');
    } else {
      document.getElementById('lama').textContent = '-';
      document.getElementById('total').textContent = '-';
    }
  }
  sewa.addEventListener('change', () => { kembali.min = sewa.value; hitung(); });
  kembali.addEventListener('change', hitung);
</script>
</body>
</html>