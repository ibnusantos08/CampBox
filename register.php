<?php
require "config/koneksi.php";
$judul = "Daftar";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $pass  = $_POST['password'];
    $pass2 = $_POST['password2'];

    if ($nama === "" || $email === "" || $pass === "") {
        $error = "Nama, email, dan password wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($pass) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($pass !== $pass2) {
        $error = "Konfirmasi password tidak sama.";
    } else {
        // cek email sudah dipakai atau belum
        $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, no_hp) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $no_hp);
            mysqli_stmt_execute($stmt);
            header("Location: login.php?daftar=sukses");
            exit;
        }
    }
}

include "includes/header.php";
?>
  <div class="container py-5" style="max-width:480px">
    <h3 class="mb-3">Daftar Akun</h3>
    <?php if ($error) { ?><div class="alert alert-danger"><?= $error ?></div><?php } ?>
    <form method="POST">
      <div class="mb-3"><label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">No. HP</label>
        <input type="text" name="no_hp" class="form-control"></div>
      <div class="mb-3"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Konfirmasi Password</label>
        <input type="password" name="password2" class="form-control" required></div>
      <button class="btn btn-success w-100">Daftar</button>
    </form>
    <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login</a></p>
  </div>
</body>
</html>