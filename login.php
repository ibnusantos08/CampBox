<?php
require "config/koneksi.php";
session_start();
$judul = "Login";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = mysqli_prepare($koneksi, "SELECT id, nama, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($pass, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Email atau password salah.";
    }
}

include "includes/header.php";
?>
  <div class="container py-5" style="max-width:480px">
    <h3 class="mb-3">Login</h3>
    <?php if (isset($_GET['daftar'])) { ?><div class="alert alert-success">Pendaftaran berhasil, silakan login.</div><?php } ?>
    <?php if ($error) { ?><div class="alert alert-danger"><?= $error ?></div><?php } ?>
    <form method="POST">
      <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-success w-100">Login</button>
    </form>
    <p class="mt-3 text-center">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</body>
</html>