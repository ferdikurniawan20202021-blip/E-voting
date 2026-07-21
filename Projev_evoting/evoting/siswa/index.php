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

$status_voting = getStatusVoting($conn);

// 2. PROSES GANTI PASSWORD DENGAN VALIDASI
if (isset($_POST['ganti_password'])) {
    $nis_input = htmlspecialchars($_POST['nis']);
    $pass_lama = $_POST['password_lama'];
    $pass_baru = $_POST['password_baru'];

    // Ambil data siswa saat ini untuk verifikasi
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
        $pesan_swal = "<script>Swal.fire('Berhasil!', 'Password berhasil diperbarui. Silakan gunakan password baru untuk login berikutnya.', 'success');</script>";
    }
}

// Cek apakah siswa sudah memilih pada tahun yang AKTIF
$stmt = $conn->prepare("SELECT status_memilih FROM siswa WHERE id_siswa = ?");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$row_siswa = $stmt->get_result()->fetch_assoc();
$sudah_memilih = $row_siswa['status_memilih'] == '1';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting | E-Voting SMK N 1 Tanjung Raya</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        /* Navbar & Header */
        .navbar-custom { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .brand-text { font-weight: 800; letter-spacing: 0.5px; color: #ffffff; }
        .brand-sub { color: #38bdf8; }
        
        .hero-banner { 
            background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%); 
            border-radius: 24px; 
            color: white; 
            padding: 40px 30px; 
            margin-top: 20px; 
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.2); 
        }
        
        /* Card Kandidat */
        .kandidat-card { border-radius: 20px; border: 1px solid #f1f5f9; transition: transform 0.3s, box-shadow 0.3s; background: #ffffff; overflow: hidden; }
        .kandidat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important; }
        
        /* FOTO KANDIDAT DIPERBAIKI */
        .kandidat-img { 
            height: 320px; /* Dipertinggi sedikit agar lebih proporsional */
            width: 100%; 
            object-fit: cover; 
            object-position: top; /* Dikembalikan ke top agar kepala tidak terpotong */
        } 
        
        .badge-nomor { position: absolute; top: 15px; left: 15px; background: #ffffff; color: #1e293b; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-weight: 800; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Buttons */
        .btn-pilih { border-radius: 12px; font-weight: 700; padding: 12px; font-size: 1.1rem; transition: all 0.2s; }
        .btn-pilih:hover { transform: scale(1.02); }
        .btn-baca { border-radius: 10px; font-weight: 600; font-size: 0.85rem; padding: 8px 12px; }
        
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
            <a class="navbar-brand d-flex align-items-center text-decoration-none" href="#">
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

    <div class="container pb-5">
        
        <!-- Hero Banner Info -->
        <div class="hero-banner mb-5 text-center text-md-start d-md-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold mb-3 shadow-sm">
                    <i class="bi bi-calendar-check me-1"></i> Tahun Ajaran Aktif: <?= $nama_tahun_aktif ?>
                </span>
                <h2 class="fw-bolder mb-2">Pemilihan Ketua OSIS</h2>
                <p class="mb-0 text-white-50 fw-medium">Gunakan hak suara Anda dengan bijak untuk kemajuan sekolah.</p>
            </div>
            <div class="mt-4 mt-md-0 d-none d-md-block">
                <i class="bi bi-envelope-paper-heart opacity-50" style="font-size: 5rem;"></i>
            </div>
        </div>

        <?php if ($status_voting != 'Sedang Berlangsung'): ?>
            <div class="alert bg-warning bg-opacity-10 border border-warning text-center rounded-4 p-5 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                <h3 class="fw-bold text-dark mt-3">Perhatian!</h3>
                <p class="text-secondary fs-5 mb-0">Sistem E-Voting saat ini: <b class="text-dark"><?= $status_voting ?></b></p>
                <p class="text-muted mt-2">Silakan hubungi administrator atau panitia penyelenggara.</p>
            </div>

        <?php elseif ($sudah_memilih): ?>
            <div class="card border-0 rounded-4 shadow-sm text-center p-5">
                <div class="card-body">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    <h2 class="fw-bold mt-4 text-dark">Terima Kasih!</h2>
                    <p class="text-muted fs-5">Anda telah berpartisipasi dan memberikan hak suara pada periode <b><?= $nama_tahun_aktif ?></b>.</p>
                    <a href="hasil.php" class="btn btn-primary px-4 py-3 mt-4 rounded-pill fw-bold shadow-sm">
                        <i class="bi bi-bar-chart-line-fill me-2"></i> Lihat Hasil Suara Sementara
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark">Kandidat Pasangan Calon</h4>
                <div class="bg-primary mx-auto rounded" style="width: 50px; height: 4px;"></div>
            </div>

            <div class="row g-4 justify-content-center">
                <?php
                $kandidat = $conn->query("SELECT * FROM kandidat WHERE id_tahun = '$id_tahun_aktif' ORDER BY nomor_urut ASC");
                if ($kandidat->num_rows > 0):
                    while ($k = $kandidat->fetch_assoc()):
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card kandidat-card h-100 shadow-sm">
                        <div class="position-relative">
                            <div class="badge-nomor"><?= str_pad($k['nomor_urut'], 2, "0", STR_PAD_LEFT) ?></div>
                            <img src="../assets/upload/<?= htmlspecialchars($k['foto'] ?? 'default.png') ?>" class="kandidat-img" alt="Foto Kandidat">
                        </div>
                        <div class="card-body text-center p-4 d-flex flex-column">
                            <h5 class="fw-bolder text-dark mb-1"><?= htmlspecialchars($k['nama_ketua']) ?></h5>
                            <p class="text-primary fw-semibold small mb-3">
                                <i class="bi bi-plus-lg me-1 small"></i><?= htmlspecialchars($k['nama_wakil']) ?>
                            </p>
                            
                            <!-- Blok Visi dan Misi (Preview & Tombol) -->
                            <div class="text-start mt-auto mb-4 bg-light p-3 rounded-3">
                                <p class="small text-dark fw-bold mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Visi:</p>
                                <p class="small text-muted mb-3 fst-italic" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    "<?= htmlspecialchars($k['visi']) ?>"
                                </p>
                                
                                <!-- Tombol Tampilkan Semua -->
                                <button type="button" class="btn btn-outline-primary w-100 btn-baca" data-bs-toggle="modal" data-bs-target="#modalVisiMisi<?= $k['id_kandidat'] ?>">
                                    <i class="bi bi-book-half me-1"></i> Baca Visi & Misi Lengkap
                                </button>
                            </div>
                            
                            <button class="btn btn-primary w-100 btn-pilih shadow-sm" data-id="<?= $k['id_kandidat'] ?>" data-nama="<?= htmlspecialchars($k['nama_ketua']) ?>">
                                <i class="bi bi-check2-circle me-1"></i> PILIH KANDIDAT
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MODAL TAMPILKAN VISI MISI LENGKAP -->
                <div class="modal fade" id="modalVisiMisi<?= $k['id_kandidat'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
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
                                    <!-- nl2br agar enter/baris baru di database terbaca -->
                                    <?= nl2br(htmlspecialchars($k['misi'])) ?>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-secondary fw-medium px-4" data-bs-dismiss="modal">Tutup</button>
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
                        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">Belum ada kandidat yang ditambahkan untuk tahun ajaran ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
                        <i class="bi bi-info-circle-fill me-1"></i> Demi keamanan, silakan verifikasi NIS dan Password Lama Anda terlebih dahulu.
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

    <script>
        document.querySelectorAll('.btn-pilih').forEach(button => {
            button.addEventListener('click', function() {
                let id = this.getAttribute('data-id');
                let nama = this.getAttribute('data-nama');
                Swal.fire({ 
                    title: 'Konfirmasi Pilihan', 
                    html: `Apakah Anda yakin ingin memberikan suara untuk kandidat<br><b class="text-primary fs-4">${nama}</b>? <br><small class="text-danger mt-2 d-block">Pilihan tidak dapat diubah setelah disimpan.</small>`, 
                    icon: 'question', 
                    showCancelButton: true, 
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#e2e8f0',
                    confirmButtonText: 'Ya, Pilih Kandidat!',
                    cancelButtonText: '<span class="text-dark">Batal</span>',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) { 
                        window.location.href = 'proses_voting.php?id=' + id; 
                    }
                })
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>