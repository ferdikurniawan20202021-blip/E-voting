<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$pesan = '';

// 1. PROSES BUKA / TUTUP VOTING
if (isset($_POST['toggle_status'])) {
    $id_tahun = $_POST['id_tahun'];
    $status_baru = $_POST['status_baru']; 
    
    $conn->query("UPDATE tahun_ajaran SET status = '$status_baru' WHERE id_tahun = '$id_tahun'");
    $pesan = "<script>Swal.fire('Berhasil!', 'Status tahun ajaran diubah menjadi $status_baru', 'success');</script>";
}

// 2. PROSES RESET SUARA
if (isset($_GET['reset_tahun'])) {
    $id_tahun = intval($_GET['reset_tahun']);
    $conn->query("DELETE FROM voting WHERE id_tahun = '$id_tahun'");
    $conn->query("UPDATE siswa SET status_memilih = '0'");
    $pesan = "<script>Swal.fire('Berhasil!', 'Data suara berhasil di-reset.', 'success');</script>";
}

$data_tahun = $conn->query("SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Voting - Admin E-Voting</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-navy: #1e3a8a;
            --primary-blue: #3b82f6;
            --accent-blue: #38bdf8;
        }

        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe !important; }
        
        /* SIDEBAR MODERN (Konsisten) */
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--primary-navy) 0%, #0f172a 100%); width: 260px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 10px; margin: 5px 15px; transition: all 0.3s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: var(--primary-blue); color: #fff; transform: translateX(5px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }
        .sidebar-logo { filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }
        
        /* CUSTOM UI MODERN UNTUK KONTEN KANAN */
        .main-content { background-color: #f4f7fe; min-height: 100vh; }
        
        /* HEADER BANNER MODERN */
        .header-banner {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #172554 100%);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
            margin-bottom: 2.5rem;
            color: #ffffff;
        }
        .title-header { color: #ffffff; font-weight: 800; letter-spacing: -0.5px; }
        .title-header span { color: var(--accent-blue); }

        /* Table & Card Modern */
        .table-card { border-radius: 20px; border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.03); background: #ffffff; }
        
        .table > :not(caption) > * > * { padding: 1.2rem 1rem; border-bottom-color: #f1f5f9; vertical-align: middle; }
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .table tbody tr { transition: all 0.2s; }
        .table tbody tr:hover { background-color: #f8fafc; }
        
        /* Buttons */
        .btn-action { padding: 8px 20px; font-size: 0.85rem; border-radius: 12px; transition: 0.3s; }
        .btn-action:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <?= $pesan ?>
    <div class="d-flex">
        
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']); 
        $is_arsip = strpos($_SERVER['REQUEST_URI'], '/arsip/') !== false;
        ?>
        <!-- SIDEBAR -->
        <div class="sidebar text-white pt-4 flex-shrink-0 shadow-lg">
            <div class="text-center mb-4 px-3">
                <img src="../assets/img/logo.png" alt="Logo SMK" width="65" class="mb-3 sidebar-logo">
                <h5 class="fw-bold mb-1 text-white" style="letter-spacing: 0.5px;">SMK N 1 TANJUNG RAYA</h5>
                <p class="small fw-bold mb-0" style="color: #38bdf8;">E-VOTING KETUA OSIS</p>
            </div>
            <hr class="border-secondary mx-3 mb-4 opacity-25">
            
            <a href="index.php" class="<?= ($current_page == 'index.php' && !$is_arsip) ? 'active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="siswa.php" class="<?= ($current_page == 'siswa.php') ? 'active' : '' ?>"><i class="bi bi-people me-2"></i> Manajemen Siswa</a>
            <a href="kandidat.php" class="<?= ($current_page == 'kandidat.php') ? 'active' : '' ?>"><i class="bi bi-person-badge me-2"></i> Manajemen Kandidat</a>
            <a href="../arsip/index.php" class="<?= ($is_arsip) ? 'active' : '' ?>"><i class="bi bi-archive me-2"></i> Data Tahun Ajaran</a>
            <a href="pengaturan.php" class="<?= ($current_page == 'pengaturan.php') ? 'active' : '' ?>"><i class="bi bi-gear me-2"></i> Pengaturan Voting</a>
            <a href="akun.php" class="<?= ($current_page == 'akun.php') ? 'active' : '' ?>"><i class="bi bi-shield-lock me-2"></i> Manajemen Akun</a>
            
            <hr class="border-secondary mx-3 mt-4 mb-3 opacity-25">
            <a href="../auth/logout.php" class="text-danger hover-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-5 w-100">
            
            <!-- HEADER BANNER MODERN -->
            <div class="header-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="title-header mb-2">Pengaturan <span>Sistem E-Voting</span></h3>
                    <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                        <i class="bi bi-sliders me-2 text-info"></i>Kelola status aktif tahun ajaran dan reset data pemilihan.
                    </p>
                </div>
            </div>
            
            <!-- Tabel Data Modern -->
            <div class="card table-card p-4">
                <div class="table-responsive">
                    <table class="table align-middle w-100">
                        <thead>
                            <tr>
                                <th style="border-top-left-radius: 12px;">Tahun Ajaran</th>
                                <th class="text-center">Status Akses</th>
                                <th class="text-center">Aksi Buka/Tutup</th>
                                <th class="text-center" style="border-top-right-radius: 12px;">Manajemen Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($t = $data_tahun->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-calendar-event text-primary me-2"></i><?= htmlspecialchars($t['nama_tahun']) ?>
                                </td>
                                <td class="text-center">
                                    <?php if($t['status'] == '1' || $t['status'] == 'Aktif'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-4 py-2 rounded-pill fw-bold">
                                            <i class="bi bi-unlock-fill me-1"></i>Voting Dibuka
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-4 py-2 rounded-pill fw-bold">
                                            <i class="bi bi-lock-fill me-1"></i>Voting Ditutup
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="id_tahun" value="<?= $t['id_tahun'] ?>">
                                        <input type="hidden" name="status_baru" value="<?= ($t['status'] == '1' || $t['status'] == 'Aktif') ? 'Tidak Aktif' : 'Aktif' ?>">
                                        <?php if($t['status'] == '1' || $t['status'] == 'Aktif'): ?>
                                            <button type="submit" name="toggle_status" class="btn btn-warning text-dark fw-bold btn-action shadow-sm">
                                                <i class="bi bi-slash-circle me-1"></i> Tutup Akses
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="toggle_status" class="btn btn-primary fw-bold btn-action shadow-sm">
                                                <i class="bi bi-check-circle me-1"></i> Buka Akses
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button onclick="confirmReset(<?= $t['id_tahun'] ?>)" class="btn btn-light border text-danger fw-bold btn-action shadow-sm">
                                        <i class="bi bi-arrow-clockwise me-1 fw-bold"></i> Reset Suara
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmReset(id) {
        Swal.fire({
            title: 'Reset Suara Tahun Ini?',
            text: "Seluruh data suara pada tahun ini akan dihapus secara permanen, dan status memilih siswa akan dikembalikan ke posisi 'Belum'!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset Data!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'pengaturan.php?reset_tahun=' + id;
            }
        })
    }
    </script>
</body>
</html>