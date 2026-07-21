<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$pesan = '';

// 1. AMBIL TAHUN AKTIF SEBAGAI DEFAULT
$q_tahun_aktif = $conn->query("SELECT * FROM tahun_ajaran WHERE status = 1");
$tahun_aktif = $q_tahun_aktif->fetch_assoc();
$id_tahun_aktif = $tahun_aktif['id_tahun'] ?? 0;
$nama_tahun_aktif = $tahun_aktif['nama_tahun'] ?? 'Belum Ada';

// Menangkap Filter Tahun
$filter_tahun = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : $id_tahun_aktif;

// Dapatkan Nama Tahun Sesuai Filter
$nama_tahun_filter = "Semua Data";
if ($filter_tahun != 'semua') {
    $q_nama_filter = $conn->query("SELECT nama_tahun FROM tahun_ajaran WHERE id_tahun = '$filter_tahun'");
    if ($q_nama_filter && $q_nama_filter->num_rows > 0) {
        $nama_tahun_filter = $q_nama_filter->fetch_assoc()['nama_tahun'];
    }
}

// ==========================================
// PROSES CRUD & FITUR BARU
// ==========================================

// A. Proses Tambah Siswa
if (isset($_POST['tambah'])) {
    $nis = htmlspecialchars($_POST['nis']);
    $nama = htmlspecialchars($_POST['nama']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $id_tahun = $_POST['id_tahun'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO siswa (nis, nama, kelas, password, id_tahun) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nis, $nama, $kelas, $password, $id_tahun);
    
    if ($stmt->execute()) {
        $pesan = "<script>Swal.fire('Berhasil!', 'Data siswa ditambahkan.', 'success');</script>";
    } else {
        $pesan = "<script>Swal.fire('Gagal!', 'NIS mungkin sudah terdaftar.', 'error');</script>";
    }
}

// B. Proses Edit Siswa
if (isset($_POST['edit'])) {
    $id_siswa = $_POST['id_siswa'];
    $nis = htmlspecialchars($_POST['nis']);
    $nama = htmlspecialchars($_POST['nama']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $id_tahun = $_POST['id_tahun'];
    
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE siswa SET nis=?, nama=?, kelas=?, password=?, id_tahun=? WHERE id_siswa=?");
        $stmt->bind_param("ssssii", $nis, $nama, $kelas, $password, $id_tahun, $id_siswa);
    } else {
        $stmt = $conn->prepare("UPDATE siswa SET nis=?, nama=?, kelas=?, id_tahun=? WHERE id_siswa=?");
        $stmt->bind_param("sssii", $nis, $nama, $kelas, $id_tahun, $id_siswa);
    }
    
    if ($stmt->execute()) {
        $pesan = "<script>Swal.fire('Berhasil!', 'Data siswa diperbarui.', 'success');</script>";
    }
}

// C. Proses Export CSV (Excel)
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="Data_Siswa_'.$nama_tahun_filter.'.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('NIS', 'Nama Siswa', 'Kelas', 'Status Memilih (1=Sudah, 0=Belum)'));
    
    $query_export = ($filter_tahun == 'semua') ? "SELECT * FROM siswa" : "SELECT * FROM siswa WHERE id_tahun = '$filter_tahun'";
    $result_export = $conn->query($query_export);
    while($row = $result_export->fetch_assoc()) {
        fputcsv($output, array($row['nis'], $row['nama'], $row['kelas'], $row['status_memilih']));
    }
    fclose($output);
    exit();
}

// D. Proses Import CSV (Excel)
if (isset($_POST['import_csv'])) {
    $filename = $_FILES["file_csv"]["tmp_name"];
    $id_tahun_import = $_POST['id_tahun_import'];
    
    if ($_FILES["file_csv"]["size"] > 0) {
        $file = fopen($filename, "r");
        fgetcsv($file); // Lewati baris pertama (header)
        
        $sukses = 0;
        while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
            $nis = $column[0] ?? '';
            $nama = $column[1] ?? '';
            $kelas = $column[2] ?? '';
            
            if(!empty($nis) && !empty($nama)) {
                $password = password_hash($nis, PASSWORD_DEFAULT); // Default password = NIS
                $conn->query("INSERT IGNORE INTO siswa (nis, nama, kelas, password, id_tahun) VALUES ('$nis', '$nama', '$kelas', '$password', '$id_tahun_import')");
                $sukses++;
            }
        }
        fclose($file);
        $pesan = "<script>Swal.fire('Selesai!', '$sukses data siswa berhasil diimpor.', 'success');</script>";
    }
}

// E. Proses Hapus Massal / Spesifik
if (isset($_POST['aksi_hapus'])) {
    $jenis_hapus = $_POST['jenis_hapus'];
    
    if ($jenis_hapus == 'pilih' && !empty($_POST['cek_siswa'])) {
        // Hapus yang dicentang
        $ids = implode(',', array_map('intval', $_POST['cek_siswa']));
        $conn->query("DELETE FROM siswa WHERE id_siswa IN ($ids)");
        $pesan = "<script>Swal.fire('Berhasil!', 'Data yang dipilih telah dihapus.', 'success');</script>";
    } elseif ($jenis_hapus == 'filter' && $filter_tahun != 'semua') {
        // Hapus berdasarkan filter saat ini
        $conn->query("DELETE FROM siswa WHERE id_tahun = '$filter_tahun'");
        $pesan = "<script>Swal.fire('Berhasil!', 'Seluruh data pada Tahun Ajaran yang difilter telah dihapus.', 'success');</script>";
    } elseif ($jenis_hapus == 'semua') {
        // Hapus semua data di tabel
        $conn->query("DELETE FROM siswa");
        $pesan = "<script>Swal.fire('Berhasil!', 'Seluruh data siswa telah dibersihkan.', 'success');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - Admin E-Voting</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

        /* Tabel & Card Modern */
        .table-card { border-radius: 18px; border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.03); background: #ffffff; }
        
        .table > :not(caption) > * > * { padding: 1.2rem 1rem; border-bottom-color: #f1f5f9; vertical-align: middle; }
        .table thead th { 
            background-color: #f8fafc; 
            color: #64748b; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 0.5px; 
            border-bottom: 2px solid #e2e8f0; 
        }
        .table tbody tr { transition: all 0.2s; }
        .table tbody tr:hover { background-color: #f8fafc; }
        
        /* Custom Checkbox */
        .form-check-input { width: 1.2em; height: 1.2em; border-color: #cbd5e1; cursor: pointer; }
        .form-check-input:checked { background-color: var(--primary-blue); border-color: var(--primary-blue); }
        
        /* Modal Customization */
        .modal-content { border-radius: 18px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .modal-header { border-bottom: 1px solid #f1f5f9; border-radius: 18px 18px 0 0; background: #ffffff; }
        .modal-footer { border-top: 1px solid #f1f5f9; background: #f8fafc; border-radius: 0 0 18px 18px; }
        .form-label { font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 10px; padding: 10px 15px; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15); border-color: #93c5fd; }
        
        /* Buttons */
        .btn { font-weight: 600; padding: 10px 20px; border-radius: 12px; transition: 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-action { font-size: 0.85rem; padding: 6px 12px; border-radius: 8px; }
        .dataTables_wrapper .row { margin-bottom: 1rem; }
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
        <!-- END SIDEBAR -->

        <!-- KONTEN UTAMA -->
        <div class="main-content flex-grow-1 p-5 w-100">
            
            <!-- HEADER BANNER MODERN -->
            <div class="header-banner d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h3 class="title-header mb-2">Manajemen <span>Data Siswa</span></h3>
                    <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                        <i class="bi bi-people-fill me-2 text-info"></i>Kelola hak suara pemilih <span class="mx-2 text-white-50">|</span> Periode Aktif: <strong class="text-white"><?= $nama_tahun_aktif ?></strong>
                    </p>
                </div>
                
                <!-- Filter Tahun dipindah ke dalam Banner -->
                <form method="GET" class="d-flex align-items-center mt-3 mt-md-0">
                    <label class="me-3 fw-bold text-white text-nowrap" style="font-size: 0.9rem;">
                        <i class="bi bi-funnel-fill text-info me-1"></i> Filter Tahun:
                    </label>
                    <select name="filter_tahun" class="form-select border-0 shadow-sm" onchange="this.form.submit()" style="width: 220px; font-size: 0.95rem; cursor: pointer;">
                        <option value="semua" <?= $filter_tahun == 'semua' ? 'selected' : '' ?>>-- Tampilkan Semua --</option>
                        <?php
                        $t_list = $conn->query("SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");
                        while($t = $t_list->fetch_assoc()):
                        ?>
                        <option value="<?= $t['id_tahun'] ?>" <?= $filter_tahun == $t['id_tahun'] ? 'selected' : '' ?>><?= $t['nama_tahun'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>

            <!-- Action Buttons Row Modern -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <!-- Dropdown Hapus -->
                <div class="dropdown">
                    <button class="btn btn-white bg-white border shadow-sm dropdown-toggle text-dark fw-bold" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-trash3-fill text-danger me-1"></i> Opsi Hapus
                    </button>
                    <ul class="dropdown-menu shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2 fw-medium" href="javascript:void(0)" onclick="eksekusiHapus('pilih')"><i class="bi bi-check2-square text-success me-2"></i>Hapus Terpilih (Centang)</a></li>
                        <?php if($filter_tahun != 'semua'): ?>
                        <li><a class="dropdown-item py-2 fw-medium" href="javascript:void(0)" onclick="eksekusiHapus('filter')"><i class="bi bi-funnel text-warning me-2"></i>Hapus Filter (<?= $nama_tahun_filter ?>)</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-bold py-2" href="javascript:void(0)" onclick="eksekusiHapus('semua')"><i class="bi bi-exclamation-triangle-fill me-2"></i>Hapus SEMUA Data</a></li>
                    </ul>
                </div>

                <!-- Tombol Navigasi Kanan (Bug Tag Penutup Sudah Diperbaiki) -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="?export=csv&filter_tahun=<?= $filter_tahun ?>" class="btn btn-success shadow-sm text-white">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>
                    
                    <button class="btn btn-info shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#modalImport">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Import Excel
                    </button>
                    
                    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Siswa
                    </button>
                </div>
            </div>

            <!-- Tabel Data Modern -->
            <form id="formSiswa" method="POST" action="">
                <input type="hidden" name="aksi_hapus" value="1">
                <input type="hidden" name="jenis_hapus" id="jenis_hapus" value="">

                <div class="card table-card">
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="tabelSiswa" class="table align-middle w-100">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 40px; border-top-left-radius: 10px;">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                        </th>
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="border-top-right-radius: 10px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $q_sql = ($filter_tahun == 'semua') ? "SELECT s.*, t.nama_tahun FROM siswa s LEFT JOIN tahun_ajaran t ON s.id_tahun = t.id_tahun ORDER BY s.id_siswa DESC" : "SELECT s.*, t.nama_tahun FROM siswa s LEFT JOIN tahun_ajaran t ON s.id_tahun = t.id_tahun WHERE s.id_tahun = '$filter_tahun' ORDER BY s.id_siswa DESC";
                                    $query = $conn->query($q_sql);
                                    
                                    while ($row = $query->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input class="form-check-input check-item" type="checkbox" name="cek_siswa[]" value="<?= $row['id_siswa'] ?>">
                                        </td>
                                        <td class="text-center text-muted fw-medium"><?= $no++ ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['nis']) ?></td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($row['nama']) ?></span>
                                            <div class="small text-muted mt-1"><i class="bi bi-clock-history me-1"></i><?= $row['nama_tahun'] ?? 'Tahun Tidak Diketahui' ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($row['kelas']) ?></span></td>
                                        <td class="text-center">
                                            <?php if($row['status_memilih'] == '1'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill fw-medium"><i class="bi bi-check-circle-fill me-1"></i>Sudah</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill fw-medium"><i class="bi bi-x-circle-fill me-1"></i>Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-light btn-action text-primary border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_siswa'] ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- END KONTEN UTAMA -->
    </div>

    <!-- MODAL EDIT SISWA -->
    <?php
    $query->data_seek(0);
    while ($row = $query->fetch_assoc()):
    ?>
    <div class="modal fade" id="modalEdit<?= $row['id_siswa'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Siswa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_siswa" value="<?= $row['id_siswa'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran Siswa</label>
                            <select name="id_tahun" class="form-select" required>
                                <?php
                                $t_list->data_seek(0);
                                while($t = $t_list->fetch_assoc()):
                                ?>
                                <option value="<?= $t['id_tahun'] ?>" <?= $row['id_tahun'] == $t['id_tahun'] ? 'selected' : '' ?>><?= $t['nama_tahun'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" class="form-control bg-light" value="<?= htmlspecialchars($row['nis']) ?>" required readonly>
                            <small class="text-muted">*NIS tidak disarankan untuk diubah</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($row['kelas']) ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit" class="btn btn-warning fw-bold text-white shadow-sm px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

    <!-- MODAL TAMBAH SISWA -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Siswa Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <select name="id_tahun" class="form-select" required>
                                <?php
                                $t_list->data_seek(0);
                                while($t = $t_list->fetch_assoc()):
                                ?>
                                <option value="<?= $t['id_tahun'] ?>" <?= $id_tahun_aktif == $t['id_tahun'] ? 'selected' : '' ?>><?= $t['nama_tahun'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">NIS</label>
                            <input type="number" name="nis" class="form-control" placeholder="Masukkan Nomor Induk Siswa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap siswa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas</label>
                            <input type="text" name="kelas" class="form-control" placeholder="Contoh: X TKJ 1" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password Akun</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password untuk login siswa" required>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary fw-bold shadow-sm px-4">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORT EXCEL -->
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-cloud-upload-fill me-2 text-info"></i>Import Data Excel (CSV)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-3 small">
                            <i class="bi bi-info-circle-fill me-1"></i> Pastikan format file CSV memiliki urutan kolom: <b>NIS, Nama Siswa, Kelas</b>. (Password otomatis menyesuaikan NIS).
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Tahun Ajaran</label>
                            <select name="id_tahun_import" class="form-select" required>
                                <?php
                                $t_list->data_seek(0);
                                while($t = $t_list->fetch_assoc()):
                                ?>
                                <option value="<?= $t['id_tahun'] ?>" <?= $id_tahun_aktif == $t['id_tahun'] ? 'selected' : '' ?>><?= $t['nama_tahun'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">File CSV</label>
                            <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                        </div>
                    </div>
                    <div class="modal-footer px-4 py-3 border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="import_csv" class="btn btn-info text-white fw-bold shadow-sm px-4">Mulai Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script External Pendukung -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Fitur Check All
        document.getElementById('checkAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.check-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Fitur SweetAlert Konfirmasi Hapus
        function eksekusiHapus(jenis) {
            document.getElementById('jenis_hapus').value = jenis;
            let pesanText = '';
            
            if (jenis === 'pilih') {
                const checked = document.querySelectorAll('.check-item:checked');
                if(checked.length === 0) {
                    Swal.fire('Peringatan', 'Silakan centang minimal satu data siswa pada tabel untuk dihapus.', 'warning');
                    return;
                }
                pesanText = 'Data siswa yang dicentang akan dihapus permanen!';
            } else if (jenis === 'filter') {
                pesanText = 'Semua data siswa pada tahun ajaran yang difilter saat ini akan dihapus!';
            } else if (jenis === 'semua') {
                pesanText = 'PERINGATAN KERAS: Seluruh data siswa dari semua tahun ajaran akan dihapus secara permanen! Apakah Anda yakin?';
            }

            Swal.fire({
                title: 'Konfirmasi Penghapusan',
                text: pesanText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formSiswa').submit();
                }
            });
        }

        // Inisialisasi DataTables
        $(document).ready(function() {
            $('#tabelSiswa').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    "search": "_INPUT_",
                    "searchPlaceholder": "Cari data siswa..."
                },
                "pageLength": 10,
                "ordering": false // Dimatikan agar tidak bertabrakan dengan urutan ID DESC default dari query
            });
            
            // Custom styling untuk input search & select datatables bawaan
            $('.dataTables_filter input').addClass('form-control shadow-sm border-0 bg-light');
            $('.dataTables_length select').addClass('form-select shadow-sm border-0 bg-light');
        });
    </script>
</body>
</html>