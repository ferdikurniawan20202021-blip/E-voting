<?php
require 'config/koneksi.php'; // Sesuaikan lokasi file koneksi Anda

// Hash password baru
$pass_admin = password_hash('admin123', PASSWORD_DEFAULT);
$pass_siswa = password_hash('siswa123', PASSWORD_DEFAULT);

// Reset Admin
if($conn->query("UPDATE admin SET password = '$pass_admin' WHERE username = 'admin'")) {
    echo "Password Admin berhasil di-reset menjadi: <b>admin123</b><br>";
}

// Reset Siswa (Semua siswa)
if($conn->query("UPDATE siswa SET password = '$pass_siswa'")) {
    echo "Password semua Siswa berhasil di-reset menjadi: <b>siswa123</b><br>";
}

echo "<br><a href='auth/login.php'>Klik di sini untuk login kembali</a>";
?>