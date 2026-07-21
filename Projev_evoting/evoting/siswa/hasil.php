<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit();
}

$id_siswa = $_SESSION['id_siswa'];
$pesan_swal = '';

// 1. AMBIL TAHUN AKTIF BESERTA NAMANYA
$q_tahun = $conn->query("SELECT id_tahun, nama_tahun FROM tahun_ajaran WHERE status = 1");
$tahun_aktif = $q_tahun->fetch_assoc();
$id_tahun_aktif = $tahun_aktif['id_tahun'] ?? 0;
$nama_tahun_aktif = $tahun_aktif['nama_tahun'] ?? 'Belum Ada';

// 2. PROSES GANTI PASSWORD DENGAN VALIDASI
if (isset($_POST['ganti_password'])) {
    $nis_input = htmlspecialchars($_POST['nis']);
    $pass_lama = $_POST['password_lama'];
    $pass_baru = $_POST['password_baru'];

    $stmt_cek = $conn->prepare("SELECT nis, password FROM siswa WHERE id_siswa = ?");
    $stmt_cek->bind_param("i", $id_siswa);
    $stmt_cek->execute();
    $data_siswa = $stmt_cek->get_result()->fetch_assoc();

    if ($data_siswa['nis'] !== $nis_input) {
        $pesan_swal = "<script>Swal.fire('Gagal!', 'NIS yang Anda masukkan tidak sesuai dengan akun ini.', 'error');</script>";
    } elseif (!password_verify($pass_lama, $data_siswa['password'])) {
        $pesan_swal = "<script>Swal.fire('Gagal!', 'Password lama yang Anda masukkan salah.', 'error');</script>";
    } else {
        $hash_baru = password_hash($pass_baru, PASSWORD_DEFAULT);
        $conn->query("UPDATE siswa SET password = '$hash_baru' WHERE id_siswa = '$id_siswa'");
        $pesan_swal = "<script>Swal.fire('Berhasil!', 'Password berhasil diperbarui.', 'success');</script>";
    }
}

// FUNGSI AMAN UNTUK MENGAMBIL ANGKA
function getAngka($conn, $sql) {
    $query = $conn->query($sql);
    if ($query) {
        $row = $query->fetch_assoc();
        return $row ? (int)current($row) : 0;
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Suara | E-Voting SMK N 1 Tanjung Raya</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        /* Navbar & Header (Biru Tua) */
        .navbar-custom { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .brand-text { font-weight: 800; letter-spacing: 0.5px; color: #ffffff; }
        .brand-sub { color: #38bdf8; }
        
        /* Card & Progress Bar Modern */
        .hasil-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); transition: transform 0.3s; background: #ffffff; overflow: hidden; }
        .hasil-card:hover { transform: translateY(-5px); }
        
        /* Penyesuaian Foto agar tidak terpotong */
        .hasil-img { height: 320px; width: 100%; object-fit: cover; object-position: top; border-bottom: 4px solid #f1f5f9; }
        
        .progress-container { background: #f1f5f9; border-radius: 12px; height: 24px; overflow: hidden; position: relative; }
        .progress-bar-custom { background: linear-gradient(90deg, #3b82f6, #60a5fa); height: 100%; display: flex; align-items: center; justify-content: flex-end; padding-right: 10px; font-weight: 700; color: white; font-size: 0.85rem; border-radius: 12px; transition: width 1s ease-in-out; }
        
        /* Modal Custom */
        .modal-content { border-radius: 20px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .form-control { border-radius: 10px; padding: 12px 15px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15); }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #64748b; text-transform: uppercase; }
    </style>
</head>
<body>
    <?= $pesan_swal ?>

    <!-- Navbar Biru -->
    <nav class="navbar navbar-expand-lg navbar-custom py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-decoration-none" href="index.php">
                <img src="../assets/img/logo.png" alt="Logo SMK" class="me-3" style="width: 50px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                <div>
                    <div class="brand-text fs-6 lh-1">SMK N 1 TANJUNG RAYA</div>
                    <div class="small fw-bold brand-sub" style="font-size: 0.8rem; margin-top: 2px;">E-VOTING KETUA OSIS</div>
                </div>
            </a>
            
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-light rounded-pill px-3 py-2 d-flex align-items-center fw-medium" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5 me-2"></i> 
                    <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                    <i class="bi bi-chevron-down ms-2 small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 15px;">
                    <li class="px-3 py-2 text-center border-bottom mb-2">
                        <small class="text-muted d-block fw-bold">NIS: <?= $_SESSION['nis'] ?? 'Siswa' ?></small>
                    </li>
                    <li><a class="dropdown-item py-2 fw-medium text-dark" href="#" data-bs-toggle="modal" data-bs-target="#modalPassword"><i class="bi bi-key me-2 text-warning"></i> Ganti Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 fw-medium text-danger" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        
        <div class="text-center mb-5">
            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold mb-3 shadow-sm border border-primary border-opacity-25">
                <i class="bi bi-calendar-check me-1"></i> Tahun Ajaran Aktif: <?= $nama_tahun_aktif ?>
            </span>
            <h2 class="fw-bolder text-dark mb-2">Live Rekapitulasi Suara</h2>
            <p class="text-muted">Hasil perolehan suara sementara yang masuk ke sistem.</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <?php
            // Hitung total suara
            $total_suara = getAngka($conn, "SELECT COUNT(*) as total FROM voting WHERE id_tahun = '$id_tahun_aktif'");
            if ($conn->error) {
                $total_suara = getAngka($conn, "SELECT COUNT(*) as total FROM voting");
            }
            
            // Ambil kandidat
            $kandidat = $conn->query("SELECT * FROM kandidat WHERE id_tahun = '$id_tahun_aktif' ORDER BY nomor_urut ASC");
            
            if ($kandidat && $kandidat->num_rows > 0) {
                while ($k = $kandidat->fetch_assoc()):
                    $id_k = $k['id_kandidat'];
                    
                    // Hitung suara
                    $suara_k = getAngka($conn, "SELECT COUNT(*) as jml FROM voting WHERE id_kandidat = '$id_k' AND id_tahun = '$id_tahun_aktif'");
                    if ($conn->error) {
                        $suara_k = getAngka($conn, "SELECT COUNT(*) as jml FROM voting WHERE id_kandidat = '$id_k'");
                    }

                    $persen = ($total_suara > 0) ? round(($suara_k / $total_suara) * 100, 1) : 0;
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="card hasil-card h-100">
                    <div class="position-relative">
                        <span class="position-absolute top-0 start-0 m-3 badge bg-white text-dark fs-6 rounded-3 px-3 py-2 shadow-sm border">
                            Paslon #<?= $k['nomor_urut'] ?>
                        </span>
                        <img src="../assets/upload/<?= htmlspecialchars($k['foto'] ?? 'default.png') ?>" class="card-img-top hasil-img" alt="Foto Paslon">
                    </div>
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($k['nama_ketua']) ?></h5>
                            <p class="text-secondary fw-medium small mb-4"><i class="bi bi-person-plus-fill me-1 opacity-50"></i>Wakil: <?= htmlspecialchars($k['nama_wakil']) ?></p>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-end mb-2">
                                <span class="fw-bold text-dark fs-5"><?= $suara_k ?> <small class="text-muted fs-6 fw-normal">Suara</small></span>
                                <span class="fw-bolder text-primary fs-5"><?= $persen ?>%</span>
                            </div>
                            <div class="progress-container w-100 shadow-sm border border-light">
                                <!-- Animasi bar diset min width 5% agar angka persentase tetap terlihat meski 0% -->
                                <div class="progress-bar-custom" style="width: <?= max($persen, 5) ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
            } else {
                echo "<div class='col-12'><div class='alert bg-white shadow-sm border p-4 text-center text-muted fw-medium rounded-4'><i class='bi bi-inboxes fs-1 d-block mb-3 opacity-50'></i>Belum ada data kandidat atau suara yang masuk pada periode ini.</div></div>";
            }
            ?>
        </div>
        
        <div class="mt-5 pb-4 text-center">
            <div class="d-inline-block bg-white shadow-sm border border-light rounded-pill px-5 py-3">
                <h6 class="text-muted mb-0 fw-bold">TOTAL KESELURUHAN SUARA MASUK: <span class="text-primary fs-4 ms-2"><?= $total_suara ?></span></h6>
            </div>
        </div>
    </div>

    <!-- Modal Ganti Password -->
    <div class="modal fade" id="modalPassword" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content">
                <div class="modal-header bg-white py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Ganti Password Akun</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i> Verifikasi NIS dan Password Lama Anda terlebih dahulu untuk mengganti password.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">NIS (Nomor Induk Siswa)</label>
                        <input type="text" name="nis" class="form-control" placeholder="Masukkan NIS Anda" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password saat ini" required>
                    </div>
                    <hr class="border-light my-4">
                    <div class="mb-2">
                        <label class="form-label text-primary">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" placeholder="Buat password baru yang kuat" required minlength="4">
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light border shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="ganti_password" class="btn btn-primary fw-bold shadow-sm px-4">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>