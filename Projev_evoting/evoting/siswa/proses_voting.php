<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'siswa' || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_siswa = $_SESSION['id_siswa'];
$id_kandidat = intval($_GET['id']);

// 1. AMBIL TAHUN AJARAN AKTIF SEBELUM PROSES VOTING
$query_tahun = $conn->query("SELECT id_tahun FROM tahun_ajaran WHERE status = 1");
$tahun_aktif = $query_tahun->fetch_assoc();
$id_tahun_aktif = $tahun_aktif['id_tahun'] ?? 0;

if ($id_tahun_aktif == 0) {
    die("Error: Tidak ada tahun ajaran yang aktif. Silakan hubungi panitia atau Admin.");
}

// Validasi 1: Cek apakah voting sedang dibuka
if (getStatusVoting($conn) != 'Sedang Berlangsung') {
    die("Voting sedang ditutup.");
}

// Validasi 2: Cek apakah sudah memilih
$cek_status = $conn->prepare("SELECT status_memilih FROM siswa WHERE id_siswa = ?");
$cek_status->bind_param("i", $id_siswa);
$cek_status->execute();
$status = $cek_status->get_result()->fetch_assoc()['status_memilih'];

if ($status == '0') {
    // Mulai transaksi Database (Konsistensi Data)
    $conn->begin_transaction();

    try {
        // PERBAIKAN DI SINI: Insert ke tabel voting beserta id_tahun
        $stmt1 = $conn->prepare("INSERT INTO voting (id_siswa, id_kandidat, id_tahun) VALUES (?, ?, ?)");
        $stmt1->bind_param("iii", $id_siswa, $id_kandidat, $id_tahun_aktif);
        $stmt1->execute();

        // Update status siswa
        $stmt2 = $conn->prepare("UPDATE siswa SET status_memilih = '1' WHERE id_siswa = ?");
        $stmt2->bind_param("i", $id_siswa);
        $stmt2->execute();

        $conn->commit();
        $_SESSION['status_memilih'] = '1';
        header("Location: index.php?pesan=sukses");
    } catch (Exception $e) {
        $conn->rollback();
        die("Terjadi kesalahan sistem: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>