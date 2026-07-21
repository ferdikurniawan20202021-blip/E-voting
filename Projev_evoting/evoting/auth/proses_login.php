<?php
session_start();
require '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $level = $_POST['level'];
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    if ($level == 'admin') {
        // Prepared statement untuk admin
        $stmt = $conn->prepare("SELECT id_admin, nama_admin, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['id_admin'] = $row['id_admin'];
                $_SESSION['nama'] = $row['nama_admin'];
                $_SESSION['level'] = 'admin';
                header("Location: ../admin/index.php");
                exit();
            }
        }
    } else if ($level == 'siswa') {
        // Prepared statement untuk siswa
        $stmt = $conn->prepare("SELECT id_siswa, nis, nama, password, status_memilih FROM siswa WHERE nis = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['id_siswa'] = $row['id_siswa'];
                $_SESSION['nis'] = $row['nis'];
                $_SESSION['nama'] = $row['nama'];
                $_SESSION['status_memilih'] = $row['status_memilih'];
                $_SESSION['level'] = 'siswa';
                header("Location: ../siswa/index.php");
                exit();
            }
        }
    }
    
    $_SESSION['error'] = "Username/NIS atau Password salah!";
    header("Location: login.php");
    exit();
}