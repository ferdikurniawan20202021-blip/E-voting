<?php 
session_start();
include '../config/koneksi.php'; 

// Cek apakah user sudah login
if(!isset($_SESSION['level'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Pemilihan | E-Voting</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
        
        /* Kartu Arsip Modern */
        .arsip-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #f1f5f9 !important;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .arsip-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important;
        }
        
        /* Efek garis atas pada kartu */
        .card-top-line {
            height: 4px;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }
        .line-active { background-color: #10b981; }
        .line-archive { background-color: var(--primary-blue); }

        /* Ikon Folder */
        .icon-box {
            width: 75px; 
            height: 75px; 
            border-radius: 22px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            margin: 0 auto;
            transition: transform 0.3s ease;
        }
        .arsip-card:hover .icon-box { transform: scale(1.1); }
        
        .btn-action { border-radius: 12px; font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- SIDEBAR -->
    <div class="sidebar text-white pt-4 flex-shrink-0 shadow-lg">
        <div class="text-center mb-4 px-3">
            <img src="../assets/img/logo.png" alt="Logo SMK" width="65" class="mb-3 sidebar-logo">
            <h5 class="fw-bold mb-1 text-white" style="letter-spacing: 0.5px;">SMK N 1 TANJUNG RAYA</h5>
            <p class="small fw-bold mb-0" style="color: #38bdf8;">E-VOTING KETUA OSIS</p>
        </div>
        <hr class="border-secondary mx-3 mb-4 opacity-25">
        
        <a href="../admin/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="../admin/siswa.php"><i class="bi bi-people me-2"></i> Manajemen Siswa</a>
        <a href="../admin/kandidat.php"><i class="bi bi-person-badge me-2"></i> Manajemen Kandidat</a>
        <a href="index.php" class="active"><i class="bi bi-archive me-2"></i> Data Tahun Ajaran</a>
        <a href="../admin/pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan Voting</a>
        <a href="../admin/akun.php"><i class="bi bi-shield-lock me-2"></i> Manajemen Akun</a>
        <hr class="border-secondary mx-3 mt-4 mb-3 opacity-25">
        <a href="../auth/logout.php" class="text-danger hover-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content flex-grow-1 p-5 w-100">
        
        <!-- HEADER BANNER MODERN -->
        <div class="header-banner d-flex justify-content-between align-items-center">
            <div>
                <h3 class="title-header mb-2">Data <span>Tahun Ajaran</span></h3>
                <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                    <i class="bi bi-archive-fill me-2 text-info"></i>Manajemen riwayat pemilihan ketua OSIS dari tahun ke tahun.
                </p>
            </div>
            <div>
                <span class="badge bg-white bg-opacity-10 text-white shadow-sm border border-light border-opacity-25 px-4 py-2 rounded-pill fw-bold" style="font-size: 0.9rem;">
                    <i class="bi bi-clock-history text-info me-2"></i>Log Sistem
                </span>
            </div>
        </div>

        <div class="row g-4">
            <?php 
            $query = mysqli_query($conn, "SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");
            
            if(mysqli_num_rows($query) > 0) {
                while($row = mysqli_fetch_assoc($query)): 
                    // Logika untuk menentukan warna berdasarkan status (Aktif vs Tidak Aktif/Arsip)
                    $is_aktif = ($row['status'] == '1' || strtolower($row['status']) == 'aktif');
                    
                    $line_class = $is_aktif ? 'line-active' : 'line-archive';
                    $badge_class = $is_aktif ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-secondary bg-opacity-10 text-secondary border-secondary';
                    $badge_text = $is_aktif ? 'Aktif Saat Ini' : 'Telah Selesai';
                    $icon_bg = $is_aktif ? 'bg-success' : 'bg-primary';
                    $icon_color = $is_aktif ? 'text-success' : 'text-primary';
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-0 arsip-card pt-3">
                    <div class="card-top-line <?= $line_class ?>"></div>
                    
                    <div class="card-body text-center p-4 d-flex flex-column">
                        <div class="mb-4">
                            <span class="badge border px-3 py-2 rounded-pill fw-semibold <?= $badge_class ?>">
                                <?php if($is_aktif): ?>
                                    <i class="bi bi-record-circle-fill me-1"></i>
                                <?php else: ?>
                                    <i class="bi bi-archive-fill me-1"></i>
                                <?php endif; ?>
                                <?= $badge_text ?>
                            </span>
                        </div>
                        
                        <div class="icon-box <?= $icon_bg ?> bg-opacity-10 mb-4">
                            <i class="bi <?= $is_aktif ? 'bi-folder-check' : 'bi-folder2-open' ?> <?= $icon_color ?>" style="font-size: 2.2rem;"></i>
                        </div>
                        
                        <h4 class="fw-bolder text-dark mb-1">T.A <?= $row['nama_tahun'] ?></h4>
                        <p class="text-muted small mb-4 fw-medium">Arsip Data E-Voting</p>
                        
                        <div class="mt-auto pt-2">
                            <a href="detail_tahun.php?id=<?= $row['id_tahun'] ?>" class="btn btn-light w-100 border btn-action text-primary fw-bold">
                                <i class="bi bi-box-arrow-in-right me-2"></i> Buka Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
            } else {
                // Tampilan jika data arsip kosong
                echo '<div class="col-12 text-center py-5">
                        <div class="p-5 bg-white rounded-4 shadow-sm border">
                            <i class="bi bi-inboxes text-muted opacity-50" style="font-size: 4rem;"></i>
                            <h4 class="text-secondary mt-3 fw-bold">Belum Ada Data Arsip</h4>
                            <p class="text-muted mb-0">Data tahun ajaran belum ditambahkan ke dalam sistem.</p>
                        </div>
                      </div>';
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>