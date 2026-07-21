<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// 1. AMBIL TAHUN AKTIF (Status 1 = Aktif)
$id_tahun_aktif = 0;
$nama_tahun_aktif = 'Belum Ada';
$query_tahun = $conn->query("SELECT id_tahun, nama_tahun FROM tahun_ajaran WHERE status = 1");

if ($query_tahun && $query_tahun->num_rows > 0) {
    $tahun_aktif = $query_tahun->fetch_assoc();
    $id_tahun_aktif = $tahun_aktif['id_tahun'];
    $nama_tahun_aktif = $tahun_aktif['nama_tahun'];
}

// 2. FUNGSI AMAN UNTUK MENGHITUNG DATA
function getCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $data = $result->fetch_assoc();
        return $data['total'] ?? 0;
    }
    return 0;
}

// 3. MENGAMBIL DATA STATISTIK (Disesuaikan dengan Tahun Aktif)
$jml_siswa = getCount($conn, "SELECT COUNT(*) as total FROM siswa"); 
$jml_kandidat = getCount($conn, "SELECT COUNT(*) as total FROM kandidat WHERE id_tahun = '$id_tahun_aktif'");
$suara_masuk = getCount($conn, "SELECT COUNT(*) as total FROM voting WHERE id_tahun = '$id_tahun_aktif'");
$belum_memilih = $jml_siswa - $suara_masuk;

// 4. MENGAMBIL DATA GRAFIK
$grafik_label = [];
$grafik_data = [];
$q_grafik = $conn->query("
    SELECT k.nomor_urut, k.nama_ketua, COUNT(v.id_voting) as total_suara 
    FROM kandidat k 
    LEFT JOIN voting v ON k.id_kandidat = v.id_kandidat AND v.id_tahun = '$id_tahun_aktif'
    WHERE k.id_tahun = '$id_tahun_aktif'
    GROUP BY k.id_kandidat ORDER BY k.nomor_urut ASC
");

if ($q_grafik) {
    while($row = $q_grafik->fetch_assoc()) {
        $grafik_label[] = "Paslon " . $row['nomor_urut'] . " (" . $row['nama_ketua'] . ")";
        $grafik_data[] = $row['total_suara'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMK N 1 Tanjung Raya</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-navy: #1e3a8a;
            --primary-blue: #3b82f6;
            --accent-blue: #38bdf8; /* Biru terang untuk highlight teks */
        }

        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe !important; }
        
        /* SIDEBAR MODERN */
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--primary-navy) 0%, #0f172a 100%); width: 260px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 10px; margin: 5px 15px; transition: all 0.3s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: var(--primary-blue); color: #fff; transform: translateX(5px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }
        
        .sidebar-logo { filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }

        /* CUSTOM UI MODERN UNTUK KONTEN KANAN */
        .main-content { background-color: #f4f7fe; min-height: 100vh; }
        
        /* HEADER BANNER (Baru: Menyesuaikan warna sidebar) */
        .header-banner {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #172554 100%);
            border-radius: 20px;
            padding: 30px 40px;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);
            margin-bottom: 2.5rem;
            color: #ffffff;
        }

        .title-header {
            color: #ffffff;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .title-header span {
            color: var(--accent-blue); /* Biru terang agar terlihat jelas di background gelap */
        }

        /* Styling Kartu Statistik */
        .stat-card { 
            border-radius: 20px; 
            border: none; 
            background: #ffffff;
            transition: all 0.3s ease; 
        }
        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.06) !important; 
        }
        
        .icon-box {
            width: 55px;
            height: 55px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            transition: all 0.3s ease;
        }
        .stat-card:hover .icon-box { transform: scale(1.1) rotate(5deg); }
        
        /* Utility */
        .tracking-wide { letter-spacing: 0.5px; }
        .chart-container { border-radius: 20px; }
        .btn-refresh { border-radius: 12px; font-weight: 600; font-size: 0.9rem; transition: 0.3s; }
        .btn-refresh:hover { background-color: #e2e8f0; transform: translateY(-2px); }
    </style>
</head>
<body>
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
        <!-- END SIDEBAR -->

        <!-- KONTEN UTAMA -->
        <div class="main-content flex-grow-1 p-5 w-100">
            
            <!-- Header Area Modern (Dengan Background Biru Gelap) -->
            <div class="header-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="title-header mb-2">Dashboard <span>Hasil Real-Time</span></h3>
                    <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                        <i class="bi bi-calendar-check me-2 text-info"></i>Periode Aktif: <strong class="text-white"><?= $nama_tahun_aktif ?></strong>
                    </p>
                </div>
                <div>
                    <!-- Warna tombol disesuaikan agar cocok di atas background gelap -->
                    <button class="btn btn-light shadow-sm btn-refresh px-4 py-2 text-dark" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise text-primary me-2 fw-bold"></i>Segarkan Data
                    </button>
                </div>
            </div>
            
            <!-- Area Cards Modern -->
            <div class="row mb-5 g-4">
                <!-- Card Total Siswa -->
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-bold mb-1 small text-uppercase tracking-wide">Total Siswa</p>
                                <h2 class="fw-bolder text-dark mb-0 fs-1"><?= $jml_siswa ?></h2>
                            </div>
                            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Suara Masuk -->
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-bold mb-1 small text-uppercase tracking-wide">Suara Masuk</p>
                                <h2 class="fw-bolder text-dark mb-0 fs-1"><?= $suara_masuk ?></h2>
                            </div>
                            <div class="icon-box bg-success bg-opacity-10 text-success">
                                <i class="bi bi-envelope-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Belum Memilih -->
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-bold mb-1 small text-uppercase tracking-wide">Belum Memilih</p>
                                <h2 class="fw-bolder text-dark mb-0 fs-1"><?= $belum_memilih ?></h2>
                            </div>
                            <div class="icon-box bg-warning bg-opacity-10 text-warning" style="color: #d97706 !important;">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Total Kandidat -->
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card shadow-sm h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted fw-bold mb-1 small text-uppercase tracking-wide">Total Kandidat</p>
                                <h2 class="fw-bolder text-dark mb-0 fs-1"><?= $jml_kandidat ?></h2>
                            </div>
                            <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-person-video2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Grafik Modern -->
            <div class="card shadow-sm border-0 chart-container">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0" style="color: var(--primary-navy) !important;">Grafik Perolehan Suara</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium">
                            <span class="spinner-grow spinner-grow-sm me-1" role="status" aria-hidden="true" style="width: 0.7rem; height: 0.7rem;"></span>
                            Live Update
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div style="height: 380px; width: 100%;"> 
                        <canvas id="votingChart"></canvas> 
                    </div>
                </div>
            </div>
        </div>
        <!-- END KONTEN UTAMA -->

    </div>

    <!-- SCRIPT GRAFIK -->
    <script>
        const ctx = document.getElementById('votingChart').getContext('2d');
        
        // Gradient Colors for Bars
        const colors = ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6'];

        const votingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($grafik_label) ?>,
                datasets: [{
                    label: 'Perolehan Suara',
                    data: <?= json_encode($grafik_data) ?>,
                    backgroundColor: colors,
                    borderRadius: 8,           
                    borderSkipped: false,      
                    maxBarThickness: 60        
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false 
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)', 
                        titleFont: { family: "'Inter', sans-serif", size: 14 },
                        bodyFont: { family: "'Inter', sans-serif", size: 14 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { 
                            display: false,    
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: "'Inter', sans-serif", weight: '600' },
                            color: '#475569'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e2e8f0', 
                            drawBorder: false,
                            borderDash: [5, 5] 
                        },
                        ticks: { 
                            stepSize: 1, 
                            font: { family: "'Inter', sans-serif", weight: '500' },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>