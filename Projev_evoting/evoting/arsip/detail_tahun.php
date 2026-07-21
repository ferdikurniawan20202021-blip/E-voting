<?php 
session_start();
include '../config/koneksi.php'; 

if(!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$id_tahun = $_GET['id'];
$query_tahun = mysqli_query($conn, "SELECT * FROM tahun_ajaran WHERE id_tahun = '$id_tahun'");
$tahun = mysqli_fetch_assoc($query_tahun);

if (!$tahun) {
    die("Data tahun ajaran tidak ditemukan.");
}

// Menghitung Total Suara untuk persentase
$q_total = mysqli_query($conn, "SELECT COUNT(id_voting) as tot FROM voting WHERE id_tahun = '$id_tahun'");
$total_suara = mysqli_fetch_assoc($q_total)['tot'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Arsip <?= $tahun['nama_tahun'] ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe !important; }
        
        /* SIDEBAR (TETAP) */
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); width: 260px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin: 5px 15px; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #3b82f6; color: #fff; transform: translateX(5px); }
        
        .main-content { background-color: #f4f7fe; min-height: 100vh; }
        
        /* Kustomisasi Tab Navigasi Modern */
        .nav-pills { background: #ffffff; padding: 8px; border-radius: 16px; border: 1px solid #f1f5f9; }
        .nav-pills .nav-link { 
            color: #64748b; 
            border-radius: 12px; 
            padding: 10px 24px; 
            font-weight: 600; 
            transition: all 0.3s ease; 
        }
        .nav-pills .nav-link:hover { color: #3b82f6; background-color: #eff6ff; }
        .nav-pills .nav-link.active { 
            background-color: #3b82f6; 
            color: #fff; 
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.25); 
        }
        
        /* Card UI */
        .content-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #ffffff; }
        
        /* Progress Bar Modern */
        .progress { height: 12px; border-radius: 20px; background-color: #f1f5f9; overflow: visible; margin-top: 10px; }
        .progress-bar { border-radius: 20px; background: linear-gradient(90deg, #3b82f6, #60a5fa); position: relative; }
        
        /* Kustomisasi Card Kandidat (Sama dgn manajemen kandidat) */
        .kandidat-card { 
            border-radius: 18px; overflow: hidden; transition: all 0.3s ease; 
            background: #ffffff; border: 1px solid #f1f5f9 !important;
        }
        .kandidat-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important; }
        .badge-nomor {
            position: absolute; top: 15px; left: 15px; background: #1e293b; color: white;
            width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
            border-radius: 10px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .kandidat-img { height: 220px; object-fit: cover; object-position: top; width: 100%; border-bottom: 1px solid #f1f5f9; }
        
        /* Tabel Data Siswa */
        .table > :not(caption) > * > * { padding: 1rem; border-bottom-color: #f1f5f9; vertical-align: middle; }
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .table tbody tr:hover { background-color: #f8fafc; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php 
    $current_page = basename($_SERVER['PHP_SELF']); 
    $is_arsip = strpos($_SERVER['REQUEST_URI'], '/arsip/') !== false;
    ?>
    <!-- SIDEBAR DINAMIS -->
    <div class="sidebar text-white pt-4 flex-shrink-0">
        <div class="text-center mb-4 px-3">
            <h4 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>E-VOTING</h4>
            <p class="text-info small fw-bold mb-0">SMK N 1 TANJUNG RAYA</p>
        </div>
        <hr class="border-secondary mx-3 mb-4">
        
        <a href="../admin/index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="../admin/siswa.php"><i class="bi bi-people me-2"></i> Manajemen Siswa</a>
        <a href="../admin/kandidat.php"><i class="bi bi-person-badge me-2"></i> Manajemen Kandidat</a>
        <a href="index.php" class="active"><i class="bi bi-archive me-2"></i> Data Tahun Ajaran</a>
        <a href="../admin/pengaturan.php"><i class="bi bi-gear me-2"></i> Pengaturan Voting</a>
        <a href="../admin/akun.php"><i class="bi bi-shield-lock me-2"></i> Manajemen Akun</a>
        <hr class="border-secondary mx-3 mt-4 mb-3">
        <a href="../auth/logout.php" class="text-danger hover-danger"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="main-content flex-grow-1 p-5 w-100">
        <!-- HEADER ARSIP -->
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom border-light pb-4">
            <div>
                <a href="index.php" class="text-decoration-none text-primary fw-medium mb-3 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Arsip
                </a>
                <h3 class="fw-bolder text-dark mb-0" style="letter-spacing: -0.5px;">
                    Data Pemilihan: <span class="text-primary"><?= $tahun['nama_tahun'] ?></span>
                </h3>
            </div>
            <div>
                <a href="../admin/cetak_pdf.php?id=<?= $id_tahun ?>" target="_blank" class="btn btn-danger shadow-sm px-4 fw-medium" style="border-radius: 12px;">
                    <i class="bi bi-file-earmark-pdf-fill me-2"></i> Cetak Laporan
                </a>
            </div>
        </div>
        
        <!-- TABS NAVIGASI -->
        <ul class="nav nav-pills mb-4 d-inline-flex shadow-sm" id="arsipTab">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#hasil"><i class="bi bi-bar-chart-fill me-2"></i> Hasil Voting</button>
            </li>
            <li class="nav-item">
                <button class="nav-link mx-1" data-bs-toggle="tab" data-bs-target="#kandidat"><i class="bi bi-person-badge-fill me-2"></i> Data Kandidat</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#siswa"><i class="bi bi-people-fill me-2"></i> Daftar Pemilih</button>
            </li>
        </ul>

        <div class="tab-content">
            
            <!-- TAB 1: HASIL VOTING -->
            <div class="tab-pane fade show active" id="hasil">
                <div class="card content-card p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h5 class="fw-bolder text-dark mb-0">Rekapitulasi Suara Masuk</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold fs-6">
                            Total: <?= $total_suara ?> Suara
                        </span>
                    </div>
                    
                    <div class="row g-4">
                        <?php 
                        $hasil = mysqli_query($conn, "
                            SELECT k.nama_ketua, k.nama_wakil, k.foto, k.nomor_urut, COUNT(v.id_voting) as total_suara 
                            FROM kandidat k 
                            LEFT JOIN voting v ON k.id_kandidat = v.id_kandidat AND v.id_tahun = '$id_tahun'
                            WHERE k.id_tahun = '$id_tahun' 
                            GROUP BY k.id_kandidat ORDER BY k.nomor_urut ASC
                        ");
                        while($h = mysqli_fetch_assoc($hasil)): 
                            $persen = ($total_suara > 0) ? round(($h['total_suara'] / $total_suara) * 100, 1) : 0;
                        ?>
                        <div class="col-12">
                            <div class="p-4 border rounded-4 bg-light bg-opacity-50 hover-shadow transition">
                                <div class="d-flex align-items-center">
                                    <div class="me-4 position-relative">
                                        <img src="../assets/upload/<?= $h['foto'] ?? 'default.png' ?>" class="rounded-circle object-fit-cover shadow-sm border border-3 border-white" width="75" height="75">
                                        <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-dark border border-2 border-white">#<?= $h['nomor_urut'] ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-end mb-2">
                                            <div>
                                                <h5 class="fw-bold text-dark mb-0"><?= $h['nama_ketua'] ?></h5>
                                                <span class="text-muted small fw-medium">Pasangan Calon No. <?= $h['nomor_urut'] ?></span>
                                            </div>
                                            <div class="text-end">
                                                <h4 class="fw-bolder text-primary mb-0"><?= $h['total_suara'] ?> <span class="fs-6 text-muted fw-normal">Suara</span></h4>
                                                <span class="fw-bold text-dark fs-6"><?= $persen ?>%</span>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?= $persen ?>%;" aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- TAB 2: KANDIDAT -->
            <div class="tab-pane fade" id="kandidat">
                <div class="row g-4">
                    <?php 
                    $kandidat = mysqli_query($conn, "SELECT * FROM kandidat WHERE id_tahun = '$id_tahun' ORDER BY nomor_urut ASC");
                    while($k = mysqli_fetch_assoc($kandidat)): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card kandidat-card h-100 shadow-sm">
                            <div class="position-relative">
                                <div class="badge-nomor"><?= str_pad($k['nomor_urut'], 2, "0", STR_PAD_LEFT) ?></div>
                                <img src="../assets/upload/<?= $k['foto'] ?? 'default.png' ?>" class="card-img-top kandidat-img" alt="Foto Paslon">
                            </div>
                            <div class="card-body text-center d-flex flex-column pt-4 px-4 pb-4">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= $k['nama_ketua'] ?>"><?= $k['nama_ketua'] ?></h5>
                                <p class="text-secondary small mb-3 fw-medium text-truncate" title="Wakil: <?= $k['nama_wakil'] ?>">
                                    <i class="bi bi-person-plus-fill me-1 opacity-50"></i> <?= $k['nama_wakil'] ?>
                                </p>
                                <hr class="border-light w-100 mt-0 mb-3">
                                <div class="text-start mt-auto">
                                    <p class="small text-primary mb-1 fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Visi Utama</p>
                                    <p class="small text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                        <?= $k['visi'] ?: '<span class="fst-italic opacity-50">Tidak ada visi yang dicantumkan.</span>' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- TAB 3: SISWA -->
            <div class="tab-pane fade" id="siswa">
                <div class="card content-card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%" style="border-top-left-radius: 12px;">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center" style="border-top-right-radius: 12px;">Status Memilih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE id_tahun = '$id_tahun' ORDER BY kelas ASC, nama ASC");
                                while($s = mysqli_fetch_assoc($siswa)): ?>
                                <tr>
                                    <td class="text-center text-muted fw-medium"><?= $no++ ?></td>
                                    <td class="fw-bold text-dark"><?= $s['nis'] ?></td>
                                    <td class="fw-medium text-dark"><?= $s['nama'] ?></td>
                                    <td><span class="badge bg-light text-dark border px-2 py-1"><?= $s['kelas'] ?></span></td>
                                    <td class="text-center">
                                        <?php if($s['status_memilih'] == '1'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-1 rounded-pill fw-medium"><i class="bi bi-check-circle-fill me-1"></i>Sudah Memilih</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1 rounded-pill fw-medium"><i class="bi bi-x-circle-fill me-1"></i>Belum Memilih</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>