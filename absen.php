<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['kelas'])) {
    die("Kelas tidak dipilih.");
}
$kelas = $_GET['kelas'];


$username = $_SESSION['username'];
$kelas = $_GET['kelas'];
$folderKelas = "data/$username/kelas/" . urlencode($kelas);
$fileSiswa = "$folderKelas/datasiswa.json";
$fileAbsen = "$folderKelas/absensi.json";

// Pastikan folder kelas ada (jaga-jaga)
if (!file_exists($folderKelas)) {
    mkdir($folderKelas, 0777, true);
}

// Ambil data siswa
$siswa = file_exists($fileSiswa) ? json_decode(file_get_contents($fileSiswa), true) : [];

// Tanggal dipilih (default = hari ini, bisa ubah via GET)
$tanggalDipilih = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Ambil data absensi yang sudah ada
$absensiHarian = file_exists($fileAbsen) ? json_decode(file_get_contents($fileAbsen), true) : [];
$absenHariIni = isset($absensiHarian[$tanggalDipilih]) ? $absensiHarian[$tanggalDipilih] : [];

// Proses simpan absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggalDipilih = $_POST['tanggal'] ?: date('Y-m-d');

    $absenHariIni = [];
    foreach ($siswa as $index => $s) {
        $status = $_POST['absen'][$index] ? : 'Alpha';
        $absenHariIni[] = [
            'nama' => $s['nama'],
            'jenis_kelamin' => $s['jenis_kelamin'],
            'status' => $status
        ];
    }

    // Simpan data absensi ke file
    $absensiHarian[$tanggalDipilih] = $absenHariIni;
    file_put_contents($fileAbsen, json_encode($absensiHarian, JSON_PRETTY_PRINT));

    $success = "Absensi tanggal $tanggalDipilih berhasil disimpan!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Absensi - Kelas <?= htmlspecialchars($kelas) ?></title>
</head>
<body class="container mt-5">

<?php include 'navbar.php'; ?>

<h3 class="text-center">Absensi Kelas</h3>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Tanggal: <?= date('d F Y', strtotime($tanggalDipilih)) ?></h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTanggal">Pilih Tanggal</button>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if (empty($siswa)): ?>
    <div class="alert alert-warning">Belum ada data siswa. Silakan <a href="datasiswa.php?kelas=<?= urlencode($kelas) ?>">tambahkan data siswa</a> terlebih dahulu.</div>
<?php else: ?>
<form method="post">
    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggalDipilih) ?>">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Jenis Kelamin</th>
                <th>Status Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siswa as $index => $s):
                $statusSebelumnya = isset($absenHariIni[$index]['status']) ? $absenHariIni[$index]['status'] : 'Alpha';
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($s['nama']) ?></td>
                <td><?= htmlspecialchars($s['jenis_kelamin']) ?></td>
                <td>
                    <select name="absen[<?= $index ?>]" class="form-select">
                        <option value="Hadir" <?= $statusSebelumnya === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                        <option value="Izin" <?= $statusSebelumnya === 'Izin' ? 'selected' : '' ?>>Izin</option>
                        <option value="Sakit" <?= $statusSebelumnya === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="Alpha" <?= $statusSebelumnya === 'Alpha' ? 'selected' : '' ?>>Alpha</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <center>
    <button type="submit" class="btn btn-success">Simpan Absensi</button>
    <div class="text-center mt-3">
    <a href="rekapan_absensi.php?kelas=<?= urlencode($kelas) ?>" class="btn btn-outline-info">Lihat Rekapan Bulanan</a>
</div>
    </center></form>
<?php endif; ?>

<!-- Modal Pilih Tanggal -->
<div class="modal fade" id="modalTanggal" tabindex="-1" aria-labelledby="modalTanggalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="get" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Tanggal Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas) ?>">
                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggalDipilih) ?>">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
