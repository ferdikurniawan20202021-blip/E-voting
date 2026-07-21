<?php
session_start();
require 'config/koneksi.php';

// Jika sudah login, arahkan sesuai level
if (isset($_SESSION['level'])) {
    if ($_SESSION['level'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: siswa/index.php");
    }
    exit();
}

// 1. AMBIL TAHUN AJARAN AKTIF
$q_tahun = $conn->query("SELECT id_tahun, nama_tahun FROM tahun_ajaran WHERE status = 1");
$tahun_aktif = $q_tahun->fetch_assoc();
$id_tahun_aktif = $tahun_aktif['id_tahun'] ?? 0;
$nama_tahun_aktif = $tahun_aktif['nama_tahun'] ?? 'Belum Ada';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang | E-Voting SMK N 1 Tanjung Raya</title>
    <!-- Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #0f172a; }
        
        /* Navbar Gelap & Menyatu */
        .navbar-custom { 
            background: rgba(24, 52, 117, 0.95); 
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px 0;
        }
        
        .brand-text { font-weight: 800; letter-spacing: 0.5px; color: #ffffff; }
        .brand-sub { color: #38bdf8; letter-spacing: 1px; }
        .logo-img { width: 55px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); }
        
        /* Animasi Pulse (Titik Live Status) */
        .pulse-dot {
            width: 10px; height: 10px; background-color: #10b981; border-radius: 50%; display: inline-block; margin-right: 8px;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .status-badge { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #e2e8f0; padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; display: flex; align-items: center; }

        /* Hero Section */
        .hero-section { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 130px 0 90px; position: relative; overflow: hidden; }
        .hero-section::before { content: ''; position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: #38bdf8; border-radius: 50%; opacity: 0.1; filter: blur(50px); }
        .hero-section::after { content: ''; position: absolute; bottom: -50px; left: -50px; width: 300px; height: 300px; background: #3b82f6; border-radius: 50%; opacity: 0.1; filter: blur(50px); }
        
        .badge-periode { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); font-weight: 600; padding: 10px 20px; font-size: 0.9rem; letter-spacing: 1px; }
        
        /* Card Kandidat */
        .kandidat-card { border-radius: 24px; border: none; transition: all 0.3s ease; background: #ffffff; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .kandidat-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .kandidat-img { height: 320px; width: 100%; object-fit: cover; object-position: top; } /* <-- Diubah ke top dan tinggi disesuaikan */
        .badge-nomor { position: absolute; top: 20px; left: 20px; background: #1e293b; color: white; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: 800; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        
        /* Langkah-langkah (Steps) */
        .step-box { background: white; border-radius: 20px; padding: 30px; text-align: center; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }
        .step-icon { width: 70px; height: 70px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 20px; }
        
        /* Buttons */
        .btn-masuk { background: #38bdf8; color: #0f172a; border-radius: 50px; font-weight: 700; padding: 14px 40px; transition: all 0.3s; border: none; font-size: 1.1rem; }
        .btn-masuk:hover { background: #7dd3fc; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(56, 189, 248, 0.4); }
        .btn-baca { border-radius: 10px; font-weight: 600; font-size: 0.85rem; padding: 8px 12px; }
    </style>
</head>
<body>

    <!-- NAVBAR GELAP MODERN -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-decoration-none" href="#">
                <img src="assets/img/logo.png" alt="Logo" class="me-3 logo-img">
                <div>
                    <!-- Susunan Teks Disamakan -->
                    <div class="brand-text lh-1 fs-5">SMK N 1 TANJUNG RAYA</div>
                    <div class="brand-sub fw-bold" style="font-size: 0.8rem; margin-top: 2px;">E-VOTING KETUA OSIS</div>
                </div>
            </a>
            
            <div class="ms-auto d-none d-md-flex align-items-center">
                <div class="status-badge fw-medium">
                    <span class="pulse-dot"></span>
                    <span class="me-3 border-end border-secondary pe-3">Sistem Online</span>
                    <span id="realtime-clock" class="text-info fw-bold" style="font-family: monospace; font-size: 1rem;">00:00:00</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section text-center">
        <div class="container position-relative z-1">
            <span class="badge badge-periode rounded-pill mb-4 shadow-sm">
                <i class="bi bi-calendar2-check-fill me-1"></i> PERIODE AKTIF: <?= htmlspecialchars($nama_tahun_aktif) ?>
            </span>
            <h1 class="display-4 fw-bolder mb-3" style="letter-spacing: -1px;">Suara Anda,<br>Masa Depan Sekolah Kita.</h1>
            <p class="lead text-secondary mb-5 mx-auto" style="max-width: 600px; font-size: 1.15rem;">
                Selamat datang di portal E-Voting SMK N 1 Tanjung Raya. Mari berpartisipasi dalam pemilihan Ketua OSIS dengan jujur, adil, dan transparan.
            </p>
            
            <a href="auth/login.php" class="btn btn-masuk shadow"><i class="bi bi-fingerprint me-2"></i> Login untuk Memilih</a>
        </div>
    </section>

    <!-- KANDIDAT SECTION -->
    <section class="py-5 mt-4">
        <div class="container">
            <div class="text-center mb-5">
                <h6 class="text-primary fw-bold letter-spacing-1 text-uppercase">Kandidat Ketua OSIS</h6>
                <h2 class="fw-bolder text-dark">Mengenal Calon Pemimpin</h2>
                <div class="bg-primary mx-auto rounded mt-3" style="width: 60px; height: 4px;"></div>
            </div>

            <div class="row g-4 justify-content-center">
                <?php
                $kandidat = $conn->query("SELECT * FROM kandidat WHERE id_tahun = '$id_tahun_aktif' ORDER BY nomor_urut ASC");
                if ($kandidat->num_rows > 0):
                    while ($k = $kandidat->fetch_assoc()):
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card kandidat-card h-100">
                        <div class="position-relative">
                            <div class="badge-nomor"><?= str_pad($k['nomor_urut'], 2, "0", STR_PAD_LEFT) ?></div>
                            <img src="assets/upload/<?= htmlspecialchars($k['foto'] ?? 'default.png') ?>" class="kandidat-img" alt="Foto Kandidat">
                        </div>
                        <div class="card-body text-center p-4 d-flex flex-column">
                            <h5 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($k['nama_ketua']) ?></h5>
                            <p class="text-primary fw-semibold small mb-3">
                                <i class="bi bi-plus-lg me-1 small"></i>Wakil: <?= htmlspecialchars($k['nama_wakil']) ?>
                            </p>
                            
                            <!-- Blok Visi dan Misi (Sama seperti di halaman siswa) -->
                            <div class="text-start mt-auto mb-2 bg-light p-3 rounded-3 border border-light">
                                <p class="small text-dark fw-bold mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Visi:</p>
                                <p class="small text-muted mb-3 fst-italic" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    "<?= htmlspecialchars($k['visi']) ?>"
                                </p>
                                
                                <button type="button" class="btn btn-outline-primary w-100 btn-baca" data-bs-toggle="modal" data-bs-target="#modalVisiMisiWelcome<?= $k['id_kandidat'] ?>">
                                    <i class="bi bi-book-half me-1"></i> Baca Visi & Misi Lengkap
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL TAMPILKAN VISI MISI LENGKAP -->
                <div class="modal fade" id="modalVisiMisiWelcome<?= $k['id_kandidat'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15);">
                            <div class="modal-header bg-white py-3 px-4 border-bottom">
                                <div>
                                    <h6 class="modal-title fw-bold text-dark mb-0">Paslon No. <?= $k['nomor_urut'] ?></h6>
                                    <small class="text-primary fw-medium"><?= htmlspecialchars($k['nama_ketua']) ?> & <?= htmlspecialchars($k['nama_wakil']) ?></small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4 text-start">
                                <h6 class="fw-bold text-dark"><i class="bi bi-lightbulb-fill text-warning me-2"></i>Visi</h6>
                                <p class="fst-italic text-muted mb-4" style="line-height: 1.6;">
                                    "<?= nl2br(htmlspecialchars($k['visi'])) ?>"
                                </p>

                                <h6 class="fw-bold text-dark"><i class="bi bi-list-check text-success me-2"></i>Misi</h6>
                                <div class="text-muted" style="line-height: 1.6;">
                                    <?= nl2br(htmlspecialchars($k['misi'])) ?>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-secondary fw-medium px-4" data-bs-dismiss="modal" style="border-radius: 10px;">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END MODAL -->
                
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-12 text-center py-5">
                        <div class="p-5 bg-white rounded-4 shadow-sm border">
                            <i class="bi bi-person-x text-muted opacity-50" style="font-size: 4rem;"></i>
                            <h5 class="fw-bold mt-3 text-secondary">Kandidat Belum Tersedia</h5>
                            <p class="text-muted mb-0">Panitia belum memasukkan data kandidat untuk periode ini.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CARA VOTING SECTION -->
    <section class="py-5 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bolder text-dark">Cara Melakukan Voting</h2>
                <p class="text-muted">Proses pemilihan sangat mudah dan aman dilakukan melalui perangkat Anda.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon"><i class="bi bi-person-vcard"></i></div>
                        <h5 class="fw-bold">1. Login Akun</h5>
                        <p class="text-muted small mb-0">Gunakan NIS (Nomor Induk Siswa) dan password Anda untuk masuk ke dalam sistem dengan aman.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon"><i class="bi bi-search-heart"></i></div>
                        <h5 class="fw-bold">2. Tentukan Pilihan</h5>
                        <p class="text-muted small mb-0">Lihat visi misi paslon, lalu tekan tombol "Pilih Kandidat" pada pasangan yang Anda inginkan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-box">
                        <div class="step-icon"><i class="bi bi-check-circle"></i></div>
                        <h5 class="fw-bold">3. Selesai</h5>
                        <p class="text-muted small mb-0">Suara Anda akan otomatis masuk secara rahasia ke dalam sistem penghitungan real-time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0 text-white-50 small">&copy; <?= date('Y') ?> E-Voting OSIS SMK N 1 Tanjung Raya. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function updateClock() {
            const now = new Date();
            let hours = String(now.getHours()).padStart(2, '0');
            let minutes = String(now.getMinutes()).padStart(2, '0');
            let seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>