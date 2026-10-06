<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// tampilkan foto alat; kalau belum ada foto, pakai kotak placeholder
if (!function_exists('tampil_gambar')) {
    function tampil_gambar($file, $nama, $tinggi = 180) {
        $path = __DIR__ . "/../assets/img/" . $file;
        if ($file && file_exists($path)) {
            return '<img src="assets/img/' . htmlspecialchars($file) . '" alt="' . htmlspecialchars($nama) . '" class="card-img-top" style="height:' . $tinggi . 'px;object-fit:cover">';
        }
        return '<div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:' . $tinggi . 'px;font-size:2.5rem">⛺</div>';
    }
}
$jumlah_keranjang = isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($judul) ? $judul . " - Campbox" : "Campbox - Sewa Alat Outdoor" ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">⛺ Campbox</a>
    <ul class="navbar-nav ms-auto align-items-center">
      <li class="nav-item"><a class="nav-link" href="katalog.php">Katalog</a></li>
      <li class="nav-item">
        <a class="nav-link" href="keranjang.php">Keranjang
          <?php if ($jumlah_keranjang > 0) { ?><span class="badge bg-light text-success"><?= $jumlah_keranjang ?></span><?php } ?>
        </a>
      </li>
      <?php if (isset($_SESSION['user_id'])) { ?>
        <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat</a></li>
        <li class="nav-item"><span class="navbar-text mx-3">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      <?php } else { ?>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Daftar</a></li>
      <?php } ?>
    </ul>
  </div>
</nav>