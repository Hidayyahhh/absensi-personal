<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_GET['kelas'])) {
    header("Location: index.php");
    exit;
}

$username = $_SESSION['username'];
$kelas = $_GET['kelas'];

$folderKelas = "data/$username/kelas/$kelas";
$fileSiswa = "$folderKelas/datasiswa.json";
$fileAbsen = "$folderKelas/absensi.json";

$siswa = file_exists($fileSiswa) ? json_decode(file_get_contents($fileSiswa), true) : [];
$absensi = file_exists($fileAbsen) ? json_decode(file_get_contents($fileAbsen), true) : [];

// Ambil bulan ini
$bulanSekarang = date('m');
$tahunSekarang = date('Y');

// Hitung tahun semester otomatis berdasarkan tanggal
$tanggalDipilih = !empty($absensi) ? array_keys($absensi)[0] : date('Y-m-d');$tanggal = new DateTime($tanggalDipilih);
$tahun = (int)$tanggal->format('Y');
$bulan = (int)$tanggal->format('m');

// Jika bulan Juli (7) ke atas, maka tahun ajaran dimulai dari tahun ini ke tahun depan
if ($bulan >= 7) {
    $tahunAjaran = "$tahun/" . ($tahun + 1);
} else {
    $tahunAjaran = ($tahun - 1) . "/$tahun";
}


// Filter absensi hanya untuk bulan sekarang
$tanggalDalamBulan = [];
foreach ($absensi as $tanggal => $list) {
    $time = strtotime($tanggal);
    if (date('m', $time) == $bulanSekarang && date('Y', $time) == $tahunSekarang) {
        $tanggalDalamBulan[date('d', $time)] = $list;
    }
}
ksort($tanggalDalamBulan); // Urutkan tanggalnya
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapan Absensi Bulanan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .absen-cell { width: 30px; text-align: center; }
        .absen-header { font-size: 12px; }
    </style>
</head>
<body class="container mt-5">

<h1 class="text-center mb-4">MI HIDAYATULLAH KOTA BLITAR</h1> 
<h5 class="text-center">Absensi <?= $tahunAjaran ?></h5>
<h5 class="text-center mb-4"><p>Bulan : <?= date('F') ?></p></h5>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light text-center absen-header">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Nama Lengkap</th>
                <th colspan="<?= count($tanggalDalamBulan) ?>">Tanggal</th>
            </tr>
            <tr>
                <?php foreach ($tanggalDalamBulan as $tgl => $d): ?>
                    <th><?= $tgl ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siswa as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($s['nama']) ?></td>
                    <?php foreach ($tanggalDalamBulan as $absenList): 
                        $status = isset($absenList[$i]['status']) ? substr($absenList[$i]['status'], 0, 1) : '-';
                    ?>
                        <td class="absen-cell"><?= $status ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</div> <!-- penutup .table-responsive -->

<!-- Tanda Tangan -->
<div class="row mt-5">
    <div class="col-6 text-center">
        <p><strong>Mengetahui</strong><br>
        Kepala Madrasah</p>
        <br><br><br>
        <p><strong>Johar Widyawati, STP</strong></p>
    </div>
    <div class="col-6 text-center">
    <br>
    <p><strong>Guru Kelas</strong></p>
    <br><br><br>
    <p><strong><?= isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : $_SESSION['username'] ?></strong></p>
</div>
</div>


<div class="mb-3">
    <br><br>
    <a href="absen.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-secondary">Kembali</a>
    <a href="ekspor_word.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-primary">Export ke Word</a>
    <a href="ekspor_excel.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-success">Export ke Excel</a>
</div>


</body>
</html>
