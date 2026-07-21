<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// AMBIL TAHUN AKTIF
$tahun_aktif = $conn->query("SELECT id_tahun, nama_tahun FROM tahun_ajaran WHERE status = 1")->fetch_assoc();
$id_tahun_aktif = $tahun_aktif['id_tahun'] ?? 0;
$nama_tahun_aktif = $tahun_aktif['nama_tahun'] ?? 'Belum Ada';

$pesan = '';
$target_dir = "../assets/upload/";

// PROSES TAMBAH KANDIDAT
if (isset($_POST['tambah'])) {
    $nomor_urut = $_POST['nomor_urut'];
    $nama_ketua = htmlspecialchars($_POST['nama_ketua']);
    $nama_wakil = htmlspecialchars($_POST['nama_wakil']);
    $visi = htmlspecialchars($_POST['visi'] ?? '');
    $misi = htmlspecialchars($_POST['misi'] ?? '');
    $id_tahun_input = $_POST['id_tahun']; 
    
    $cek_duplikat = $conn->query("SELECT * FROM kandidat WHERE nomor_urut = '$nomor_urut' AND id_tahun = '$id_tahun_input'");
    
    if ($cek_duplikat->num_rows > 0) {
        $pesan = "<script>Swal.fire('Gagal!', 'Nomor urut sudah dipakai pada tahun ajaran ini.', 'error');</script>";
    } else {
        $foto = $_FILES['foto']['name'];
        $tmp_name = $_FILES['foto']['tmp_name'];
        $ukuran = $_FILES['foto']['size'];
        $ext = pathinfo($foto, PATHINFO_EXTENSION);
        $ext_boleh = array('jpg', 'jpeg', 'png');
        $nama_foto_baru = "kandidat_".$nomor_urut."_".time().".".$ext;

        if (in_array(strtolower($ext), $ext_boleh)) {
            if ($ukuran <= 2000000) {
                move_uploaded_file($tmp_name, $target_dir.$nama_foto_baru);
                $stmt = $conn->prepare("INSERT INTO kandidat (nomor_urut, nama_ketua, nama_wakil, foto, visi, misi, id_tahun) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssi", $nomor_urut, $nama_ketua, $nama_wakil, $nama_foto_baru, $visi, $misi, $id_tahun_input);
                if ($stmt->execute()) {
                    $pesan = "<script>Swal.fire('Berhasil!', 'Data kandidat ditambahkan.', 'success');</script>";
                }
            } else {
                $pesan = "<script>Swal.fire('Gagal!', 'Ukuran foto maksimal 2MB.', 'error');</script>";
            }
        } else {
            $pesan = "<script>Swal.fire('Gagal!', 'Ekstensi file harus JPG/PNG.', 'error');</script>";
        }
    }
}

// PROSES EDIT KANDIDAT
if (isset($_POST['edit'])) {
    $id_kandidat = $_POST['id_kandidat'];
    $nomor_urut = $_POST['nomor_urut'];
    $nama_ketua = htmlspecialchars($_POST['nama_ketua']);
    $nama_wakil = htmlspecialchars($_POST['nama_wakil']);
    $visi = htmlspecialchars($_POST['visi'] ?? '');
    $misi = htmlspecialchars($_POST['misi'] ?? '');
    $foto_lama = $_POST['foto_lama'];

    if (!empty($_FILES['foto']['name'])) {
        $foto = $_FILES['foto']['name'];
        $ext = pathinfo($foto, PATHINFO_EXTENSION);
        $nama_foto_baru = "kandidat_".$nomor_urut."_".time().".".$ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir.$nama_foto_baru);
        if (file_exists($target_dir.$foto_lama) && $foto_lama != 'default.png') unlink($target_dir.$foto_lama);
        
        $stmt = $conn->prepare("UPDATE kandidat SET nomor_urut=?, nama_ketua=?, nama_wakil=?, foto=?, visi=?, misi=? WHERE id_kandidat=?");
        $stmt->bind_param("isssssi", $nomor_urut, $nama_ketua, $nama_wakil, $nama_foto_baru, $visi, $misi, $id_kandidat);
    } else {
        $stmt = $conn->prepare("UPDATE kandidat SET nomor_urut=?, nama_ketua=?, nama_wakil=?, visi=?, misi=? WHERE id_kandidat=?");
        $stmt->bind_param("issssi", $nomor_urut, $nama_ketua, $nama_wakil, $visi, $misi, $id_kandidat);
    }
    if (isset($stmt) && $stmt->execute()) {
        $pesan = "<script>Swal.fire('Berhasil!', 'Data kandidat diperbarui.', 'success');</script>";
    }
}

// PROSES HAPUS KANDIDAT
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $q_foto = $conn->query("SELECT foto FROM kandidat WHERE id_kandidat = $id");
    $data_foto = $q_foto->fetch_assoc();
    if ($data_foto && file_exists($target_dir.$data_foto['foto']) && $data_foto['foto'] != 'default.png') unlink($target_dir.$data_foto['foto']);
    $conn->query("DELETE FROM kandidat WHERE id_kandidat = $id");
    header("Location: kandidat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Kandidat - Admin E-Voting</title>
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
        
        /* SIDEBAR MODERN (Sama dengan Dashboard) */
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--primary-navy) 0%, #0f172a 100%); width: 260px; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 10px; margin: 5px 15px; transition: all 0.3s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: var(--primary-blue); color: #fff; transform: translateX(5px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); }
        .sidebar-logo { filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }

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
        
        /* Kartu Kandidat */
        .kandidat-card { 
            border-radius: 18px; 
            overflow: hidden; 
            transition: all 0.3s ease; 
            background: #ffffff;
            border: 1px solid #f1f5f9 !important;
        }
        .kandidat-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important; 
        }
        
        /* Foto & Badge */
        .img-wrapper { position: relative; }
        .badge-nomor {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-blue);
            color: white;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 800;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            border: 3px solid #fff;
        }
        
        /* Modal Customization */
        .modal-content { border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
        .modal-header { border-bottom: 1px solid #f1f5f9; background: #ffffff; border-radius: 16px 16px 0 0; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 10px; padding: 10px 15px; border-color: #e2e8f0; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15); border-color: #93c5fd; }
        
        /* Buttons */
        .btn-action { border-radius: 10px; font-weight: 600; font-size: 0.85rem; padding: 8px; transition: 0.3s; }
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
        <!-- END SIDEBAR -->

        <!-- KONTEN UTAMA -->
        <div class="main-content flex-grow-1 p-5 w-100">
            
            <!-- Header Area Modern (Dengan Background Biru Gelap) -->
            <div class="header-banner d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="title-header mb-2">Manajemen <span>Kandidat</span></h3>
                    <p class="mb-0 fw-medium" style="color: #cbd5e1;">
                        <i class="bi bi-person-badge me-2 text-info"></i>Kelola data paslon ketua dan wakil <span class="mx-2 text-white-50">|</span> Periode Aktif: <strong class="text-white"><?= $nama_tahun_aktif ?></strong>
                    </p>
                </div>
                <button class="btn btn-light px-4 py-2 fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="bi bi-plus-lg me-2 text-primary"></i> Tambah Kandidat
                </button>
            </div>
            
            <!-- Grid Kandidat -->
            <div class="row g-4">
                <?php
                $kandidat = $conn->query("SELECT * FROM kandidat WHERE id_tahun = '$id_tahun_aktif' ORDER BY nomor_urut ASC");
                if ($kandidat->num_rows > 0) {
                    while ($k = $kandidat->fetch_assoc()) {
                ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card kandidat-card h-100 shadow-sm">
                        <div class="img-wrapper">
                            <img src="../assets/upload/<?= $k['foto'] ?? 'default.png' ?>" class="card-img-top w-100" style="height: 250px; object-fit: cover; object-position: top;">
                            <div class="badge-nomor text-center">
                                <?= str_pad($k['nomor_urut'], 2, "0", STR_PAD_LEFT) ?>
                            </div>
                        </div>
                        <div class="card-body text-center d-flex flex-column pt-4 px-4 pb-4">
                            <h5 class="fw-bolder text-dark mb-1 text-truncate" title="<?= $k['nama_ketua'] ?>"><?= $k['nama_ketua'] ?></h5>
                            <p class="text-primary small mb-4 fw-semibold text-truncate" title="Wakil: <?= $k['nama_wakil'] ?>">
                                <i class="bi bi-plus-lg me-1 small"></i> <?= $k['nama_wakil'] ?>
                            </p>
                            
                            <div class="mt-auto d-flex gap-2">
                                <button class="btn btn-light border btn-action w-50 text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id_kandidat'] ?>">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>
                                <button class="btn btn-light border btn-action w-50 text-danger shadow-sm" onclick="hapusKandidat(<?= $k['id_kandidat'] ?>)">
                                    <i class="bi bi-trash3 me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Edit -->
                <div class="modal fade" id="modalEdit<?= $k['id_kandidat'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header py-3 px-4">
                                <h6 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Kandidat</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_kandidat" value="<?= $k['id_kandidat'] ?>">
                                    <input type="hidden" name="foto_lama" value="<?= $k['foto'] ?? 'default.png' ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nomor Urut</label>
                                        <input type="number" name="nomor_urut" class="form-control" value="<?= $k['nomor_urut'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nama Ketua</label>
                                        <input type="text" name="nama_ketua" class="form-control" value="<?= $k['nama_ketua'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nama Wakil</label>
                                        <input type="text" name="nama_wakil" class="form-control" value="<?= $k['nama_wakil'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Visi</label>
                                        <textarea name="visi" class="form-control" rows="3"><?= $k['visi'] ?? '' ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Misi</label>
                                        <textarea name="misi" class="form-control" rows="3"><?= $k['misi'] ?? '' ?></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Foto Baru (Opsional)</label>
                                        <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                                        <small class="text-muted">*Kosongkan jika tidak ingin mengubah foto</small>
                                    </div>
                                    <button type="submit" name="edit" class="btn btn-warning w-100 fw-bold py-2 text-white shadow-sm">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    } 
                } else {
                    // Tampilan jika data kandidat kosong
                    echo '<div class="col-12 text-center py-5">
                            <div class="p-5 bg-white rounded-4 shadow-sm border">
                                <i class="bi bi-person-x text-muted opacity-50" style="font-size: 4rem;"></i>
                                <h5 class="fw-bold mt-3 text-secondary">Belum ada data kandidat tahun ini.</h5>
                                <p class="text-muted mb-0">Silakan klik tombol "Tambah Kandidat" di pojok kanan atas.</p>
                            </div>
                          </div>';
                }
                ?>
            </div>
        </div>
        <!-- END KONTEN UTAMA -->
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-3 px-4">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Kandidat Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <select name="id_tahun" class="form-select form-control" required>
                                <?php
                                $t_list = $conn->query("SELECT * FROM tahun_ajaran ORDER BY nama_tahun DESC");
                                while($t = $t_list->fetch_assoc()) {
                                    $selected = ($t['id_tahun'] == $id_tahun_aktif) ? 'selected' : '';
                                    echo "<option value='".$t['id_tahun']."' $selected>".$t['nama_tahun']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Urut</label>
                            <input type="number" name="nomor_urut" class="form-control" placeholder="Contoh: 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Ketua</label>
                            <input type="text" name="nama_ketua" class="form-control" placeholder="Masukkan nama ketua" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Wakil</label>
                            <input type="text" name="nama_wakil" class="form-control" placeholder="Masukkan nama wakil" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Visi</label>
                            <textarea name="visi" class="form-control" rows="3" placeholder="Tuliskan visi paslon..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Misi</label>
                            <textarea name="misi" class="form-control" rows="3" placeholder="Tuliskan misi paslon..."></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Foto Paslon (JPG/PNG - Max 2MB)</label>
                            <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        </div>
                        <button type="submit" name="tambah" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">Simpan Data Kandidat</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Kodingan Javascript yang sebelumnya terpotong sudah diperbaiki secara utuh di sini
        function hapusKandidat(id){
            Swal.fire({
                title: 'Hapus Kandidat?',
                text: "Data kandidat dan foto akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true // Membalikkan posisi tombol agar lebih natural (Batal di kiri, Hapus di kanan)
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?hapus=' + id;
                }
            })
        }
    </script>
</body>
</html>