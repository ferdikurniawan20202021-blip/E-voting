<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$pesan = '';

// Proses Tambah Admin Baru
if (isset($_POST['tambah_admin'])) {
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama_admin']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin (username, nama_admin, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $nama, $password);
    if ($stmt->execute()) {
        $pesan = "<script>Swal.fire('Berhasil!', 'Admin baru ditambahkan.', 'success');</script>";
    }
}

// Proses Edit Akun
if (isset($_POST['edit_akun'])) {
    $id = $_POST['id'];
    $tabel = $_POST['tabel']; 
    $username = htmlspecialchars($_POST['username']);
    $nama = htmlspecialchars($_POST['nama']);
    
    if (!empty($_POST['password'])) {
        // JIKA PASSWORD DIISI
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        if ($tabel == 'admin') {
            $stmt = $conn->prepare("UPDATE admin SET username=?, nama_admin=?, password=? WHERE id_admin=?");
            $stmt->bind_param("sssi", $username, $nama, $password, $id);
        } else {
            $kelas = htmlspecialchars($_POST['kelas']);
            $stmt = $conn->prepare("UPDATE siswa SET nis=?, nama=?, kelas=?, password=? WHERE id_siswa=?");
            $stmt->bind_param("ssssi", $username, $nama, $kelas, $password, $id);
        }
    } else {
        // JIKA PASSWORD KOSONG
        if ($tabel == 'admin') {
            // HANYA UPDATE USERNAME DAN NAMA
            $stmt = $conn->prepare("UPDATE admin SET username=?, nama_admin=? WHERE id_admin=?");
            $stmt->bind_param("ssi", $username, $nama, $id);
        } else {
            $kelas = htmlspecialchars($_POST['kelas']);
            $stmt = $conn->prepare("UPDATE siswa SET nis=?, nama=?, kelas=? WHERE id_siswa=?");
            $stmt->bind_param("sssi", $username, $nama, $kelas, $id);
        }
    }
    
    if ($stmt->execute()) {
        $pesan = "<script>Swal.fire('Berhasil!', 'Akun berhasil diperbarui.', 'success');</script>";
    } else {
        $pesan = "<script>Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Akun - Admin E-Voting</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- DataTables CSS untuk tabel Siswa -->
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

        /* Card & Table Modern */
        .table-card { border-radius: 20px; border: none; box-shadow: 0 4px 25px rgba(0,0,0,0.03); background: #ffffff; }
        .card-header { background: #ffffff; border-bottom: 1px solid #f1f5f9; border-radius: 20px 20px 0 0 !important; padding: 1.2rem 1.5rem; }
        
        .table > :not(caption) > * > * { padding: 1rem; border-bottom-color: #f1f5f9; vertical-align: middle; }
        .table thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .table tbody tr { transition: all 0.2s; }
        .table tbody tr:hover { background-color: #f8fafc; }
        
        /* Modal Customization */
        .modal-content { border-radius: 18px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .modal-header { border-bottom: 1px solid #f1f5f9; border-radius: 18px 18px 0 0; background: #ffffff;}
        .modal-footer { border-top: 1px solid #f1f5f9; background: #f8fafc; border-radius: 0 0 18px 18px; }
        .form-label { font-weight: 600; font-size: 0.8rem; color: #475569; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;}
        .form-control { border-radius: 10px; padding: 10px 15px; border-color: #e2e8f0; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15); border-color: #93c5fd; }
        
        /* DataTables Adjustments */
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

        <div class="main-content flex-grow-1 p-5 w-100">
            
            <!-- HEADER BANNER MODERN -->
            <div class="header-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="title-header mb-2">Manajemen <span>Akun Pengguna</span></h3>
                    <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                        <i class="bi bi-shield-lock me-2 text-info"></i>Kelola kredensial login Administrator dan Siswa.
                    </p>
                </div>
            </div>

            <!-- CARD ADMIN -->
            <div class="card table-card mb-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bolder text-dark"><i class="bi bi-person-workspace me-2 text-primary fs-5"></i>Data Administrator</span>
                    <button class="btn btn-primary fw-bold px-3 shadow-sm" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#tambahAdmin">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Admin
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th style="border-top-left-radius: 12px;">Username</th>
                                    <th>Nama Lengkap</th>
                                    <th class="text-center" style="border-top-right-radius: 12px; width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $admins = $conn->query("SELECT * FROM admin");
                                while($a = $admins->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><i class="bi bi-at text-muted me-1"></i><?= $a['username'] ?></td>
                                    <td class="fw-medium text-secondary"><?= $a['nama_admin'] ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-light border text-primary btn-sm px-3 shadow-sm fw-medium" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#editAdmin<?= $a['id_admin'] ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Modal Edit Admin -->
                                <div class="modal fade" id="editAdmin<?= $a['id_admin'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header py-3 px-4">
                                                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-shield-check me-2 text-warning"></i>Edit Akun Admin</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="id" value="<?= $a['id_admin'] ?>">
                                                    <input type="hidden" name="tabel" value="admin">
                                                    <div class="mb-3">
                                                        <label class="form-label">Username</label>
                                                        <input type="text" name="username" class="form-control" value="<?= $a['username'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Lengkap</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= $a['nama_admin'] ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Password Baru (Opsional)</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                    </div>
                                                </div>
                                                <div class="modal-footer px-4 py-3 border-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_akun" class="btn btn-warning text-white fw-bold shadow-sm px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Tambah Admin -->
            <div class="modal fade" id="tambahAdmin" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-3 px-4">
                            <h6 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Akun Admin Baru</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" placeholder="Buat username untuk login" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama_admin" class="form-control" placeholder="Nama lengkap admin" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Password Akses</label>
                                    <input type="password" name="password" class="form-control" placeholder="Buat password yang kuat" required>
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="tambah_admin" class="btn btn-primary fw-bold shadow-sm px-4">Simpan Admin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CARD SISWA -->
            <div class="card table-card">
                <div class="card-header bg-white py-3">
                    <span class="fw-bolder text-dark"><i class="bi bi-people-fill me-2 text-success fs-5"></i>Data Akun Siswa</span>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="tabelSiswa" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>NIS / Username</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $siswas = $conn->query("SELECT * FROM siswa ORDER BY kelas ASC, nama ASC");
                                while($s = $siswas->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= $s['nis'] ?></td>
                                    <td class="fw-medium text-secondary"><?= $s['nama'] ?></td>
                                    <td><span class="badge bg-light text-dark border px-2 py-1"><?= $s['kelas'] ?></span></td>
                                    <td class="text-center">
                                        <button class="btn btn-light border text-primary btn-sm px-3 shadow-sm fw-medium" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#editSiswa<?= $s['id_siswa'] ?>">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Modal Edit Siswa -->
                                <div class="modal fade" id="editSiswa<?= $s['id_siswa'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header py-3 px-4">
                                                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-person-lines-fill me-2 text-warning"></i>Edit Akun Siswa</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body p-4">
                                                    <input type="hidden" name="id" value="<?= $s['id_siswa'] ?>">
                                                    <input type="hidden" name="tabel" value="siswa">
                                                    
                                                    <div class="alert alert-info border-0 rounded-3 small">
                                                        <i class="bi bi-info-circle-fill me-1"></i> NIS digunakan sebagai Username untuk login siswa.
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">NIS (Username)</label>
                                                        <input type="text" name="username" class="form-control" value="<?= $s['nis'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Lengkap</label>
                                                        <input type="text" name="nama" class="form-control" value="<?= $s['nama'] ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kelas</label>
                                                        <input type="text" name="kelas" class="form-control" value="<?= $s['kelas'] ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Password Baru (Opsional)</label>
                                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                    </div>
                                                </div>
                                                <div class="modal-footer px-4 py-3 border-0">
                                                    <!-- KODE YANG SEBELUMNYA TERPOTONG MULAI DARI SINI -->
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" name="edit_akun" class="btn btn-warning text-white fw-bold shadow-sm px-4">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script External Pendukung -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inisialisasi DataTables untuk tabel siswa
        $(document).ready(function() {
            $('#tabelSiswa').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    "search": "_INPUT_",
                    "searchPlaceholder": "Cari data akun siswa..."
                },
                "pageLength": 10,
                "ordering": true
            });
            
            // Custom styling untuk input search & select datatables
            $('.dataTables_filter input').addClass('form-control shadow-sm border-0 bg-light');
            $('.dataTables_length select').addClass('form-select shadow-sm border-0 bg-light');
        });
    </script>
</body>
</html>