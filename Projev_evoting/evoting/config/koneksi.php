<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "evoting_osis";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Fungsi helper untuk mengecek status voting
function getStatusVoting($conn) {
    $result = $conn->query("SELECT status_voting FROM pengaturan WHERE id_pengaturan = 1");
    $row = $result->fetch_assoc();
    return $row['status_voting'];
}
?>