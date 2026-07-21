PHP
<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require '../assets/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// AMBIL ID TAHUN DARI URL (Jika tidak ada, ambil yang aktif)
$id_tahun = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_tahun == 0) {
    $q_aktif = $conn->query("SELECT id_tahun FROM tahun_ajaran WHERE status = 1 LIMIT 1");
    $id_tahun = $q_aktif->fetch_assoc()['id_tahun'] ?? 0;
}

// AMBIL DATA TAHUN
$tahun = $conn->query("SELECT * FROM tahun_ajaran WHERE id_tahun = '$id_tahun'")->fetch_assoc();
$nama_tahun = $tahun['nama_tahun'] ?? 'Tahun Tidak Diketahui';

// Mengambil Data Agregat berdasarkan tahun
$total_siswa = $conn->query("SELECT COUNT(*) as total FROM siswa WHERE id_tahun = '$id_tahun'")->fetch_assoc()['total'];
$total_suara = $conn->query("SELECT COUNT(*) as total FROM voting WHERE id_tahun = '$id_tahun'")->fetch_assoc()['total'];
$belum_memilih = $total_siswa - $total_suara;
$partisipasi = ($total_siswa > 0) ? round(($total_suara / $total_siswa) * 100, 2) : 0;

// Mengambil Data Perolehan Suara Kandidat berdasarkan tahun
$query = $conn->query("
    SELECT k.nomor_urut, k.nama_ketua, k.nama_wakil, COUNT(v.id_voting) as suara 
    FROM kandidat k 
    LEFT JOIN voting v ON k.id_kandidat = v.id_kandidat AND v.id_tahun = '$id_tahun'
    WHERE k.id_tahun = '$id_tahun'
    GROUP BY k.id_kandidat ORDER BY k.nomor_urut ASC
");

// Mulai merangkai kerangka HTML untuk PDF
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil E-Voting</title>
    <style>
        /* Mengubah font menjadi Times New Roman standar dokumen resmi */
        body { font-family: "Times New Roman", Times, serif; color: #000; font-size: 12pt; }
        
        .kop-surat { text-align: center; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 30px; }
        .kop-surat h2 { margin: 0; font-size: 16pt; text-transform: uppercase; letter-spacing: 1px; font-weight: normal;}
        .kop-surat h1 { margin: 5px 0; font-size: 20pt; font-weight: bold; }
        .kop-surat p { margin: 0; font-size: 12pt; }
        
        .info-statistik { margin-bottom: 20px; }
        .info-statistik table { width: 60%; }
        .info-statistik td { padding: 3px 0; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 8px; text-align: center; }
        .table-data th { background-color: #e5e5e5; font-weight: bold; text-transform: uppercase; }
        .text-left { text-align: left !important; }
        
        .tanda-tangan { width: 100%; margin-top: 40px; }
        .tanda-tangan td { text-align: center; padding: 10px; }
        .nama-terang { font-weight: bold; text-decoration: underline; margin-top: 70px; }
    </style>
</head>
<body>

    <div class="kop-surat">
        <h2>Laporan Resmi Hasil Pemilihan Ketua OSIS</h2>
        <h1>SMK N 1 TANJUNG RAYA</h1>
        <p>Tahun Pelajaran '.$nama_tahun.'</p>
    </div>

    <div class="info-statistik">
        <table>
            <tr><td width="55%">Total Daftar Pemilih Tetap (Siswa)</td><td>: <b>'.$total_siswa.'</b> Orang</td></tr>
            <tr><td>Total Suara Masuk</td><td>: <b>'.$total_suara.'</b> Suara</td></tr>
            <tr><td>Siswa Belum Memilih (Golput)</td><td>: <b>'.$belum_memilih.'</b> Orang</td></tr>
            <tr><td>Tingkat Partisipasi</td><td>: <b>'.$partisipasi.'%</b></td></tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th width="10%">No. Urut</th>
                <th width="45%" class="text-left">Nama Pasangan Kandidat</th>
                <th width="25%">Perolehan Suara</th>
                <th width="20%">Persentase</th>
            </tr>
        </thead>
        <tbody>';

        $kandidat_terpilih = '-';
        $suara_tertinggi = -1;

        while($row = $query->fetch_assoc()){
            $persentase_kandidat = ($total_suara > 0) ? round(($row['suara'] / $total_suara) * 100, 2) : 0;
            
            // Mencari pemenang
            if ($row['suara'] > $suara_tertinggi) {
                $suara_tertinggi = $row['suara'];
                $kandidat_terpilih = $row['nama_ketua'] . ' & ' . $row['nama_wakil'];
            }

            $html .= '<tr>
                <td>0'.$row['nomor_urut'].'</td>
                <td class="text-left"><strong>'.$row['nama_ketua'].'</strong> <br> <small>& '.$row['nama_wakil'].'</small></td>
                <td><b style="font-size: 14pt;">'.$row['suara'].'</b></td>
                <td>'.$persentase_kandidat.'%</td>
            </tr>';
        }

$html .= '
        </tbody>
    </table>

    <p style="text-align: justify; text-indent: 30px; line-height: 1.5;">
        Berdasarkan hasil pemungutan suara elektronik (E-Voting) yang telah dilaksanakan, menetapkan bahwa kandidat terpilih dengan perolehan suara terbanyak adalah pasangan <b>'.$kandidat_terpilih.'</b>. Demikian laporan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <table class="tanda-tangan">
        <tr>
            <td width="50%">
                <p>Mengetahui,<br>Kepala Sekolah SMK N 1 Tanjung Raya</p>
                <div class="nama-terang">...............................................</div>
                <p>NIP. ........................................</p>
            </td>
            <td width="50%">
                <p>Tanjung Raya, '.date('d F Y').'<br>Ketua Panitia Pemilihan</p>
                <div class="nama-terang">...............................................</div>
                <p>NIS. ........................................</p>
            </td>
        </tr>
    </table>

</body>
</html>';

// Konfigurasi Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultPaperSize', 'A4');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Render HTML ke PDF
$dompdf->render();

// Output dokumen PDF ke browser
$dompdf->stream("Laporan_Hasil_Voting_SMKN1TanjungRaya.pdf", array("Attachment" => 0));
?>